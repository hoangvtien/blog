<?php

/**
 * @Project NUKEVIET BLOG 3.x
 * @Author PHAN TAN DUNG (phantandung92@gmail.com)
 * @Copyright (C) 2013 PHAN TAN DUNG. All rights reserved
 * @Createdate Dec 11, 2013, 09:50:11 PM
 */

if ( ! defined( 'NV_IS_MOD_BLOG' ) ) die( 'Stop!!!' );

// Breadcrumbs
$array_mod_title[] = array(
	'catid' => 0,
	'title' => 'Tags',
	'link' => NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=" . $op,
);

// Xem chi tiết tags
if( isset( $array_op[1] ) )
{
	// Kiểm tra hợp chuẩn URL
	unset( $m );
	if( isset( $array_op[3] ) or ( isset( $array_op[2] ) and ! preg_match( "/^page\-([0-9]+)$/i", $array_op[2], $m ) ) )
	{
		header( 'Location:' . nv_url_rewrite( NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name, true ) );
		die();
	}
	
	// Xác định số trang
	$page = 1;
	if( ! empty( $m ) )
	{
		$page = intval( $m[1] );
	}
	$per_page = intval( $BL->setting['numPostPerPage'] );
	
	// Lấy thông tin của tags
	$sql = "SELECT * FROM `" . $BL->table_prefix . "_tags` WHERE `alias`=" . $db->dbescape( $array_op[1] );
	$result = $db->sql_query( $sql );
	
	if( $db->sql_numrows( $result ) != 1 ){
		header( 'Location:' . nv_url_rewrite( NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name, true ) );
		die();
	}
	
	$array_tags = $db->sql_fetch_assoc( $result );
	
	// Xác định tiêu đề của trang
	$page_title = $mod_title = $array_tags['title'];
	
	if( ! empty( $array_tags['keywords'] ) )
	{
		$key_words = $array_tags['keywords'];
	}
	
	$description = $array_tags['description'];
	
	if( empty( $description ) )
	{
		$description = $BL->lang('detailDefDescription') . ' ' . $array_tags['title'];
	}
	
	// Thêm vào breadcrumbs
	$array_mod_title[] = array(
		'catid' => 1,
		'title' => $array_tags['title'],
		'link' => NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=" . $op . '/' . $array_tags['alias'],
	);
	
	// SQL lấy các bài viết
	$sql = "FROM `" . $BL->table_prefix . "_rows` WHERE `status`=1 AND (" . $BL->build_query_search_id( $array_tags['id'], 'tagids' ) . ")";
	$base_url = NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=" . $op . '/' . $array_tags['alias'];
	
	// Lay so row
	$sql1 = "SELECT COUNT(*) " . $sql;
	$result1 = $db->sql_query( $sql1 );
	list( $all_page ) = $db->sql_fetchrow( $result1 );
	
	// Lay du lieu
	$sql = "SELECT * " . $sql . " ORDER BY `pubTime` DESC LIMIT " . ( ( $page - 1 ) * $per_page ) . ", " . $per_page;
	$result = $db->sql_query( $sql );
	
	$array = $array_userids = array();
	
	while( $row = $db->sql_fetch_assoc( $result ) )
	{
		$row['mediaType'] = intval( $row['mediaType'] );
		$row['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $row['alias'];
		$row['postName'] = '';
		
		// Xac dinh media
		if( $row['mediaType'] == 0 )
		{
			$row['mediaValue'] = $row['images'];
		}
		
		if( ! empty( $row['mediaValue'] ) )
		{
			if( is_file( NV_UPLOADS_REAL_DIR . '/' . $module_name . $row['mediaValue'] ) )
			{
				$row['mediaValue'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . $row['mediaValue'];
			}
			elseif( ! nv_is_url( $row['mediaValue'] ) )
			{
				$row['mediaValue'] = '';
			}
		}
	
		// Xac dinh images
		if( ! empty( $row['images'] ) )
		{
			if( is_file( NV_UPLOADS_REAL_DIR . '/' . $module_name . $row['images'] ) )
			{
				$row['images'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_name . $row['images'];
			}
			elseif( ! nv_is_url( $row['images'] ) )
			{
				$row['images'] = '';
			}
		}
		
		$array[$row['id']] = $row;
		$array_userids[$row['postid']] = $row['postid'];
	}
	
	// Khong cho dat $page tuy y
	if( $page > 1 and empty( $array ) )
	{
		header( 'Location:' . nv_url_rewrite( NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=" . $module_name, true ) );
		die();
	}
	
	// Lay thanh vien dang bai
	if( ! empty( $array_userids ) )
	{
		$sql = "SELECT `userid`, `username` FROM `" . NV_USERS_GLOBALTABLE . "` WHERE `userid` IN(" . implode( ",", $array_userids ) . ")";
		$result = $db->sql_query( $sql );
		
		$array_userids = array();
		while( $row = $db->sql_fetchrow( $result ) )
		{
			$array_userids[$row['userid']] = $row['username'];
		}
		
		foreach( $array as $row )
		{
			if( isset( $array_userids[$row['postid']] ) )
			{
				$array[$row['id']]['postName'] = $array_userids[$row['postid']];
			}
		}
	}
	
	// Du lieu phan trang
	$generate_page = $BL->pagination( $page_title, $base_url, $all_page, $per_page, $page );
	$total_pages = ceil( $all_page / $per_page );
	
	// Them vao tieu de neu phan trang
	if( $page > 1 )
	{
		$page_title .= ' ' . NV_TITLEBAR_DEFIS . ' ' . $BL->glang('page') . ' ' . $page;
		
		if( ! empty( $key_words ) )
		{
			$key_words .= ', ' . $BL->glang('page') . ' ' . $page;
		}
		
		$description .= ' ' . NV_TITLEBAR_DEFIS . ' ' . $BL->glang('page') . ' ' . $page;
	}
	
	$contents = nv_detail_tags_theme( $array, $generate_page, $BL->setting, $page, $total_pages, $BL );
	
	include ( NV_ROOTDIR . "/includes/header.php" );
	echo nv_site_theme( $contents );
	include ( NV_ROOTDIR . "/includes/footer.php" );
	die();
}
// Tất cả các tags
else
{
	$page_title = $mod_title = $BL->lang('allTags');
	$key_words = $module_info['keywords'] . ', tags';
	$description = $module_info['description'] . ' tags';
	
	$sql = "SELECT `id`, `title`, `alias`, `numPosts` FROM `" . $BL->table_prefix . "_tags` ORDER BY `title` ASC";
	$result = $db->sql_query( $sql );
	
	$array = array();
	while( $row = $db->sql_fetch_assoc( $result ) )
	{
		$array[$row['id']] = $row;
		$array[$row['id']]['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '/' . $row['alias'];
	}
	
	$contents = nv_all_tags_theme( $array, $BL );
	
	include ( NV_ROOTDIR . "/includes/header.php" );
	echo nv_site_theme( $contents );
	include ( NV_ROOTDIR . "/includes/footer.php" );
	die();
}

?>