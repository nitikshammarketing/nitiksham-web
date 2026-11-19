<?php
/**
 * Plugin Name: Nitiksham Studio Backend
 * Description: WordPress authentication, private media and REST APIs for the original Nitiksham Studio frontend.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Requires PHP: 8.0
 * Author: Nitiksham Marketing
 * License: GPL-2.0-or-later
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

const NSM_MAX_FILE_SIZE = 15728640; // 15 MiB
const NSM_API_NAMESPACE = 'nitiksham/v1';

function nsm_table() {
    global $wpdb;
    return $wpdb->prefix . 'nitiksham_records';
}

function nsm_install() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = nsm_table();
    $sql = "CREATE TABLE $table (
      id varchar(40) NOT NULL,
      kind varchar(24) NOT NULL,
      title varchar(191) NOT NULL,
      description text NOT NULL,
      mime varchar(80) NOT NULL DEFAULT '',
      published tinyint(1) NOT NULL DEFAULT 0,
      archived tinyint(1) NOT NULL DEFAULT 0,
      live_url text NULL,
      demo_url text NULL,
      owner_id bigint(20) unsigned NOT NULL DEFAULT 0,
      created_at datetime NOT NULL,
      file_bytes longblob NULL,
      PRIMARY KEY  (id),
      KEY published_kind (published,kind)
    ) " . $wpdb->get_charset_collate() . ';';
    dbDelta( $sql );
    $exists = $wpdb->get_var( $wpdb->prepare("SELECT id FROM $table WHERE id=%s",'stockdesk') ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    if ( ! $exists ) {
        $wpdb->insert( $table, array(
            'id' => 'stockdesk', 'kind' => 'software', 'title' => 'Stockdesk',
            'description' => 'Plumbing inventory and stock management workspace.',
            'mime' => '', 'published' => 1, 'archived' => 0,
            'owner_id' => get_current_user_id(), 'created_at' => '2026-11-18 00:00:00',
        ) );
    }
}
register_activation_hook( __FILE__, 'nsm_install' );

function nsm_record($row, $admin=false) {
    $r = array(
        'id' => (string) $row->id,
        'kind' => (string) $row->kind,
        'title' => (string) $row->title,
        'description' => (string) $row->description,
        'mime' => (string) $row->mime,
        'published' => (bool) $row->published,
        'createdAt' => gmdate('Y-m-d\TH:i:s\Z', strtotime($row->created_at . ' UTC')),
    );
    if ($admin) {
        $r['archived'] = (bool) $row->archived;
        if ($row->kind === 'software') {
            $r['liveUrl'] = (string) $row->live_url;
            $r['demoUrl'] = (string) $row->demo_url;
        }
    }
    return $r;
}

function nsm_error($message,$status=400) {
    return new WP_Error('nitiksham_error',$message,array('status'=>$status));
}
function nsm_admin_permission($request) {
    return current_user_can('manage_options') && wp_verify_nonce($request->get_header('X-WP-Nonce'),'wp_rest');
}
function nsm_uuid_ok($id) {
    return is_string($id) && (bool) preg_match('/^[a-z0-9-]{1,40}$/D',$id);
}
function nsm_get_row($id) {
    global $wpdb;
    if ( ! nsm_uuid_ok($id) ) return null;
    $t=nsm_table();
    return $wpdb->get_row($wpdb->prepare("SELECT id,kind,title,description,mime,published,archived,live_url,demo_url,owner_id,created_at FROM $t WHERE id=%s",$id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}
function nsm_list_rows($filter='all') {
    global $wpdb;
    $t=nsm_table();
    $where='';
    if ($filter==='gallery') $where="WHERE published=1 AND archived=0 AND kind IN ('design','newsletter')";
    elseif ($filter==='software') $where="WHERE published=1 AND archived=0 AND kind='software'";
    return $wpdb->get_results("SELECT id,kind,title,description,mime,published,archived,live_url,demo_url,owner_id,created_at FROM $t $where ORDER BY created_at DESC"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}
function nsm_session($request) {
    $admin=(bool) current_user_can('manage_options');
    $user=wp_get_current_user();
    return rest_ensure_response(array('authenticated'=>is_user_logged_in(),'owner'=>$admin,'email'=>$admin ? $user->user_email : null));
}
function nsm_gallery($request) {
    return rest_ensure_response(array_map(function($row) { return nsm_record($row); },nsm_list_rows('gallery')));
}
function nsm_software($request) {
    return rest_ensure_response(array_map(function($row) {
        $r=nsm_record($row);
        return array_intersect_key($r,array_flip(array('id','title','description')));
    },nsm_list_rows('software')));
}
function nsm_manage_items($request) {
    return rest_ensure_response(array_map(function($row) {return nsm_record($row,true);},nsm_list_rows()));
}

function nsm_file_mime($bytes) {
    if (strlen($bytes)>=3 && substr($bytes,0,3)==="\xFF\xD8\xFF") return 'image/jpeg';
    if (strlen($bytes)>=8 && substr($bytes,0,8)==="\x89PNG\r\n\x1a\n") return 'image/png';
    if (strlen($bytes)>=12 && substr($bytes,0,4)==='RIFF' && substr($bytes,8,4)==='WEBP') return 'image/webp';
    if (strlen($bytes)>=5 && substr($bytes,0,5)==='%PDF-') return 'application/pdf';
    return null;
}

function nsm_manage_upload($request) {
    global $wpdb;
    $kind=(string) $request->get_param('kind');
    $title=trim((string)$request->get_param('title'));
    $desc=(string)$request->get_param('description');
    if (!in_array($kind,array('design','newsletter'),true) || !$title || mb_strlen($title)>120) return nsm_error('Enter a title and valid category.');
    $files=$request->get_file_params();
    if (empty($files['file']) || is_array($files['file']['tmp_name']??null)) return nsm_error('Choose a file up to 15 MB.');
    $file=$files['file'];
    $size=(int)($file['size']??0);
    if (!empty($file['error']) || $size<1 || $size>NSM_MAX_FILE_SIZE) return nsm_error('Choose a file up to 15 MB.');
    $temp=$file['tmp_name']??'';
    if (!$temp || !is_uploaded_file($temp)) return nsm_error('Invalid uploaded file.');
    $bytes=file_get_contents($temp);
    if ( $bytes===false || strlen($bytes)!==$size ) return nsm_error('Upload failed.',500);
    $mime=nsm_file_mime($bytes);
    if (!$mime) return nsm_error('Only valid JPG, PNG, WebP and PDF files are supported.');
    $id=wp_generate_uuid4();
    $row=array(
        'id'=>$id, 'kind'=>$kind,'title'=>sanitize_text_field($title),
        'description'=>sanitize_textarea_field(mb_substr($desc,0,1000)),
        'mime'=>$mime,'published'=>filter_var($request->get_param('published'),FILTER_VALIDATE_BOOLEAN)?1:0,
        'archived'=>0,'owner_id'=>get_current_user_id(),
        'created_at'=>current_time('mysql',true),'file_bytes'=>$bytes,
    );
    if ($wpdb->insert(nsm_table(),$row)===false) return nsm_error('Could not save the file. Check the database upload limit.',500);
    $saved=nsm_get_row($id);
    return new WP_REST_Response(nsm_record($saved,true),201);
}

function nsm_https_url($raw) {
    $url=trim((string)$raw);
    if ($url==='') return '';
    if (!filter_var($url,FILTER_VALIDATE_URL)) return false;
    $parts=wp_parse_url($url);
    if (!$parts || strtolower($parts['scheme']??'')!=='https' || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) return false;
    return esc_url_raw($url,array('https'));
}
function nsm_manage_software($request) {
    global $wpdb;
    $data=$request->get_json_params();
    if (!is_array($data)) return nsm_error('Invalid JSON.');
    $title=trim((string)($data['title']??''));
    if (!$title || mb_strlen($title)>120) return nsm_error('Enter a software name (up to 120 characters).');
    $id=(string)($data['id']??'');
    if ($id==='') $id=wp_generate_uuid4();
    if (!nsm_uuid_ok($id)) return nsm_error('Invalid software ID.');
    $existing=nsm_get_row($id);
    if ($existing && $existing->kind!=='software') return nsm_error('Invalid software record.');
    $live=nsm_https_url($data['liveUrl']??'');
    $demo=nsm_https_url($data['demoUrl']??'');
    if ($live===false || $demo===false) return nsm_error('Use an HTTPS URL without a username or password.');
    $row=array(
        'kind'=>'software','title'=>sanitize_text_field($title),
        'description'=>sanitize_textarea_field(mb_substr((string)($data['description']??''),0,1000)),
        'live_url'=>$live,'demo_url'=>$demo,
        'published'=>!empty($data['published'])?1:0,
    );
    if ($existing) $ok=$wpdb->update(nsm_table(),$row,array('id'=>$id));
    else {
        $row['id']=$id;$row['mime']='';$row['archived']=0;$row['owner_id']=get_current_user_id();$row['created_at']=current_time('mysql',true);
        $ok=$wpdb->insert(nsm_table(),$row);
    }
    if ($ok===false) return nsm_error('Could not save software.',500);
    return new WP_REST_Response(nsm_record(nsm_get_row($id),true),201);
}
function nsm_manage_patch($request) {
    global $wpdb;
    $id=(string)$request['id'];
    if (!nsm_uuid_ok($id)) return nsm_error('Invalid ID.');
    $row=nsm_get_row($id);
    if (!$row) return nsm_error('Not found.',404);
    $data=$request->get_json_params();
    if (!is_array($data)) return nsm_error('Invalid JSON.');
    $changes=array();
    if (array_key_exists('published',$data) && is_bool($data['published'])) $changes['published']=$data['published']?1:0;
    if (array_key_exists('archived',$data) && is_bool($data['archived'])) $changes['archived']=$data['archived']?1:0;
    if (isset($data['title']) && is_string($data['title'])) {
        $title=trim($data['title']);
        if (!$title || mb_strlen($title)>120) return nsm_error('Enter a valid title.');
        $changes['title']=sanitize_text_field($title);
    }
    if ($changes && $wpdb->update(nsm_table(),$changes,array('id'=>$id))===false) return nsm_error('Could not save changes.',500);
    return rest_ensure_response(nsm_record(nsm_get_row($id),true));
}

function nsm_register_routes() {
    $public='__return_true';
    $admin='nsm_admin_permission';
    register_rest_route(NSM_API_NAMESPACE,'/session',array('methods'=>'GET','callback'=>'nsm_session','permission_callback'=>$public));
    register_rest_route(NSM_API_NAMESPACE,'/gallery',array('methods'=>'GET','callback'=>'nsm_gallery','permission_callback'=>$public));
    register_rest_route(NSM_API_NAMESPACE,'/software',array('methods'=>'GET','callback'=>'nsm_software','permission_callback'=>$public));
    register_rest_route(NSM_API_NAMESPACE,'/manage/items',array('methods'=>'GET','callback'=>'nsm_manage_items','permission_callback'=>$admin));
    register_rest_route(NSM_API_NAMESPACE,'/manage/upload',array('methods'=>'POST','callback'=>'nsm_manage_upload','permission_callback'=>$admin));
    register_rest_route(NSM_API_NAMESPACE,'/manage/software',array('methods'=>'POST','callback'=>'nsm_manage_software','permission_callback'=>$admin));
    register_rest_route(NSM_API_NAMESPACE,'/manage/items/(?P<id>[a-z0-9-]{1,40})',array('methods'=>'PATCH','callback'=>'nsm_manage_patch','permission_callback'=>$admin));
}
add_action('rest_api_init','nsm_register_routes');

// Public files are served only when published and not archived. Private data never has a direct uploads URL.
function nsm_file_output() {
    global $wpdb;
    $id=isset($_GET['id']) ? sanitize_text_field(wp_unslash($_GET['id'])) : '';
    if (!nsm_uuid_ok($id)) { status_header(404); exit; }
    $t=nsm_table();
    $row=$wpdb->get_row($wpdb->prepare("SELECT id,kind,mime,published,archived,file_bytes FROM $t WHERE id=%s",$id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    if (!$row || !$row->file_bytes || $row->kind==='software' || $row->archived || (!$row->published && !current_user_can('manage_options'))) {
        status_header(404); exit;
    }
    $mime=$row->mime;
    $safe=array('image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf');
    if (!isset($safe[$mime])) { status_header(404); exit; }
    nocache_headers();
    header('Content-Type: '.$mime);
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: default-src 'none'; sandbox");
    header('Content-Disposition: '.($mime==='application/pdf'?'attachment':'inline').'; filename="'.$id.'.'.$safe[$mime].'"');
    header('Content-Length: '.strlen($row->file_bytes));
    echo $row->file_bytes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- validated bytes
    exit;
}
add_action('wp_ajax_nitiksham_file','nsm_file_output');
add_action('wp_ajax_nopriv_nitiksham_file','nsm_file_output');
