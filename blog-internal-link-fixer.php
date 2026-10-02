<?php
/**
 * Plugin Name: Blog Internal Link Fixer
 * Description: Safely finds and selectively fixes incorrect internal blog links, including WPBakery post content.
 * Version: 1.1.0
 * Author: AbdulRehman Khokhar
 * License: GPL-2.0-or-later
 */
if (!defined('ABSPATH')) exit;

final class BILF_Plugin {
    const VERSION='1.1.0';
    const NONCE='bilf_nonce';

    public function __construct(){
        add_action('admin_menu',[$this,'admin_menu']);
        add_action('admin_enqueue_scripts',[$this,'assets']);
        add_action('wp_ajax_bilf_scan',[$this,'ajax_scan']);
        add_action('wp_ajax_bilf_fix',[$this,'ajax_fix']);
    }

    public function admin_menu(){
        add_management_page('Blog Link Fixer','Blog Link Fixer','manage_options','blog-internal-link-fixer',[$this,'page']);
    }

    public function assets($hook){
        if($hook!=='tools_page_blog-internal-link-fixer') return;
        wp_enqueue_style('bilf-admin',plugin_dir_url(__FILE__).'assets/admin.css',[],self::VERSION);
        wp_enqueue_script('bilf-admin',plugin_dir_url(__FILE__).'assets/admin.js',['jquery'],self::VERSION,true);
        wp_localize_script('bilf-admin','BILF',['ajax'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce(self::NONCE)]);
    }

    public function page(){
        $prefix=$this->get_blog_prefix(); ?>
        <div class="wrap bilf-wrap">
            <h1>Blog Internal Link Fixer</h1>
            <p class="description">Scan published blog posts for internal links that match another published blog post by slug but use the wrong URL structure.</p>
            <div class="bilf-card bilf-settings">
                <div><strong>Site:</strong> <code><?php echo esc_html(home_url('/')); ?></code></div>
                <div><strong>Detected blog prefix:</strong> <code id="bilf-prefix"><?php echo esc_html($prefix); ?></code></div>
                <button type="button" class="button button-primary" id="bilf-scan">Scan Blog Posts</button>
            </div>
            <div id="bilf-status" class="bilf-status" hidden></div>
            <div id="bilf-results" hidden></div>
        </div><?php
    }

    private function get_blog_prefix(){
        $c=[];
        $page=get_page_by_path('blog');
        if($page && $page->post_status==='publish') $c[]='/blog/';
        $posts_page=(int)get_option('page_for_posts');
        if($posts_page){
            $path=get_page_uri($posts_page);
            if($path) $c[]='/'.trim($path,'/').'/';
        }
        if(in_array('/blog/',$c,true)) return '/blog/';
        return !empty($c)?$c[0]:'/blog/';
    }

    private function get_post_map(){
        $ids=get_posts([
            'post_type'=>'post','post_status'=>'publish','posts_per_page'=>-1,
            'orderby'=>'ID','order'=>'ASC','fields'=>'ids'
        ]);
        $map=[];
        foreach($ids as $id){
            $slug=get_post_field('post_name',$id);
            if($slug) $map[strtolower($slug)]=['id'=>$id,'url'=>get_permalink($id),'title'=>get_the_title($id)];
        }
        return $map;
    }

    private function normalize_path($url){
        $path=wp_parse_url($url,PHP_URL_PATH);
        if($path===null || $path===false) return '/';
        $path=rawurldecode($path);
        $path=preg_replace('#/+#','/',$path);
        return '/'.trim($path,'/').'/';
    }

    private function is_same_site($url){
        $parts=wp_parse_url($url);
        if(!$parts) return false;
        $host=isset($parts['host'])?strtolower($parts['host']):'';
        $site=strtolower(wp_parse_url(home_url('/'),PHP_URL_HOST));
        if($host==='') return true;
        $host=preg_replace('/^www\./i','',$host);
        $site=preg_replace('/^www\./i','',$site);
        return $host===$site;
    }

    private function absolute_url($url){
        if($url==='') return '';
        if(preg_match('#^https?://#i',$url)) return $url;
        if(strpos($url,'//')===0) return (is_ssl()?'https:':'http:').$url;
        return home_url('/'.ltrim($url,'/'));
    }

    private function find_issues_for_post($post_id,$post_map,$prefix){
        // WPBakery stores its layouts as WordPress shortcodes in post_content.
        // We scan raw content and never reserialize the entire document.
        $content=get_post_field('post_content',$post_id);
        if(!$content) return [];
        $issues=[];
        $pattern='~<a\b[^>]*?\bhref\s*=\s*(["\'])(.*?)\1[^>]*>~is';
        if(!preg_match_all($pattern,$content,$matches,PREG_OFFSET_CAPTURE)) return [];

        foreach($matches[2] as $href_match){
            $href_raw=$href_match[0];
            $href=html_entity_decode(trim($href_raw),ENT_QUOTES|ENT_HTML5,'UTF-8');
            if($href==='' || preg_match('#^(#|mailto:|tel:|javascript:|data:|sms:)#i',$href)) continue;

            $absolute=$this->absolute_url($href);
            if(!$this->is_same_site($absolute)) continue;
            $path=$this->normalize_path($absolute);
            if(trim($path,'')==='') continue;
            if($prefix!=='/' && strpos($path,$prefix)===0) continue;

            $segments=array_values(array_filter(explode('/',trim($path,'/'))));
            if(!$segments) continue;
            $slug=strtolower(rawurldecode(end($segments)));
            if($slug==='' || !isset($post_map[$slug])) continue;

            $target=$post_map[$slug];
            $canonical=$target['url'];
            if($path===$this->normalize_path($canonical)) continue;

            $issues[]=[
                'source_post_id'=>$post_id,'source_title'=>get_the_title($post_id),
                'source_url'=>get_permalink($post_id),'old_url'=>$href_raw,
                'absolute_old'=>$absolute,'new_url'=>$canonical,
                'target_post_id'=>$target['id'],'target_title'=>$target['title'],'slug'=>$slug
            ];
        }
        return $issues;
    }

    public function ajax_scan(){
        check_ajax_referer(self::NONCE,'nonce');
        if(!current_user_can('manage_options')) wp_send_json_error(['message'=>'Permission denied.']);
        @set_time_limit(300);
        $prefix=$this->get_blog_prefix();
        $map=$this->get_post_map();
        $ids=get_posts(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>-1,'orderby'=>'date','order'=>'DESC','fields'=>'ids']);
        $issues=[];
        foreach($ids as $id) $issues=array_merge($issues,$this->find_issues_for_post($id,$map,$prefix));
        wp_send_json_success(['prefix'=>$prefix,'posts_scanned'=>count($ids),'issues'=>$issues]);
    }

    public function ajax_fix(){
        check_ajax_referer(self::NONCE,'nonce');
        if(!current_user_can('manage_options')) wp_send_json_error(['message'=>'Permission denied.']);
        @set_time_limit(300);
        $items=isset($_POST['items'])?json_decode(wp_unslash($_POST['items']),true):null;
        if(!is_array($items)) wp_send_json_error(['message'=>'Invalid selection.']);
        $changed=0;$skipped=0;$details=[];
        foreach($items as $item){
            $source=isset($item['source_post_id'])?absint($item['source_post_id']):0;
            $target=isset($item['target_post_id'])?absint($item['target_post_id']):0;
            $old=isset($item['old_url'])?(string)$item['old_url']:'';
            if(!$source||!$target||$old===''){ $skipped++; continue; }

            if(get_post_type($source)!=='post'||get_post_status($source)!=='publish'||get_post_type($target)!=='post'||get_post_status($target)!=='publish'){
                $skipped++;continue;
            }
            $new=get_permalink($target);
            $content=get_post_field('post_content',$source);
            if(!$content||!$new){$skipped++;continue;}

            $updated=$this->replace_exact_anchor_href($content,$old,$new);
            if($updated===$content){$skipped++;$details[]='Skipped: '.get_the_title($source).' — exact link no longer found.';continue;}

            $result=wp_update_post(['ID'=>$source,'post_content'=>$updated],true);
            if(is_wp_error($result)){
                $skipped++;$details[]='Failed: '.get_the_title($source).' — '.$result->get_error_message();
            }else{$changed++;$details[]='Updated: '.get_the_title($source);}
        }
        wp_send_json_success(['changed'=>$changed,'skipped'=>$skipped,'details'=>$details]);
    }

    private function replace_exact_anchor_href($content,$old_url,$new_url){
        // Preserve WPBakery shortcodes and all original content; replace only one exact href.
        $pattern='~(<a\b[^>]*?\bhref\s*=\s*)(["\'])'.preg_quote($old_url,'~').'(\2)~is';
        $count=0;
        $updated=preg_replace_callback($pattern,function($m)use($new_url,&$count){
            if($count>0) return $m[0];
            $count++;
            return $m[1].$m[2].esc_url($new_url).$m[3];
        },$content,1);
        return ($updated!==null&&$count===1)?$updated:$content;
    }
}
new BILF_Plugin();
