<?php

/**
 * @Project NUKEVIET BLOG 4.x
 * @Author PHAN TAN DUNG (phantandung92@gmail.com)
 * @Copyright (C) 2014 PHAN TAN DUNG. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Dec 11, 2013, 09:50:11 PM
 */

if (!defined('NV_MAINFILE'))
    die('Stop!!!');

if (!nv_function_exists('nv_blog_newsletters')) {
    function nv_blog_newsletters($block_config)
    {
        global $module_info, $global_config, $site_mods, $client_info, $module_name;

        $module = $block_config['module'];
        $module_file = $site_mods[$module]['module_file'];

        if (file_exists(NV_ROOTDIR . "/themes/" . $module_info['template'] . "/modules/" . $module_file . "/block.newsletters.tpl")) {
            $block_theme = $module_info['template'];
        } elseif (file_exists(NV_ROOTDIR . "/themes/" . $global_config['site_theme'] . "/modules/" . $module_file . "/block.newsletters.tpl")) {
            $block_theme = $global_config['site_theme'];
        } else {
            $block_theme = "default";
        }

        // Goi css
        if ($module_name != $module and !defined('NV_IS_BLOG_CSS')) {
            global $my_head;

            $css_file = 'themes/' . $block_theme . '/css/' . $module_file . '.css';

            if (file_exists(NV_ROOTDIR . '/' . $css_file)) {
                define('NV_IS_BLOG_CSS', true);

                $my_head .= "<link rel=\"stylesheet\" href=\"" . NV_BASE_SITEURL . $css_file . "\"/>\n";
            }
        }

        // Goi ngon ngu module
        include (NV_ROOTDIR . "/modules/" . $module_file . "/language/" . NV_LANG_DATA . ".php");

        $xtpl = new XTemplate("block.newsletters.tpl", NV_ROOTDIR . "/themes/" . $block_theme . "/modules/" . $module_file);
        $xtpl->assign("LANG", $lang_module);
        $xtpl->assign("CHECKSESS", md5($global_config['sitekey'] . $client_info['session_id']));
        $xtpl->assign("MODULE_NAME", $module);

        $xtpl->parse('main');
        return $xtpl->text('main');
    }
}

if (defined('NV_SYSTEM')) {
    $content = nv_blog_newsletters($block_config);
}
