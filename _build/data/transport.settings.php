<?php
/**
 * chimpx
 *
 * Copyright 2011 by Romain Tripault <romain@melting-media.com>
 *
 * chimpx is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * chimpx is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * chimpx; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package chimpx
 */
/**
 * Loads system settings into build
 *
 * @package chimpx
 * @subpackage build
 */

$settings = array();

$settings['chimpx.apikey']= $modx->newObject('modSystemSetting');
$settings['chimpx.apikey']->fromArray(array(
    'key' => 'chimpx.apikey',
    'value' => '',
    'xtype' => 'text-password',
    'namespace' => 'chimpx',
    'area' => '',
),'',true,true);
$settings['chimpx.templates']= $modx->newObject('modSystemSetting');
$settings['chimpx.templates']->fromArray(array(
    'key' => 'chimpx.templates',
    'value' => '',
    'xtype' => 'text',
    'namespace' => 'chimpx',
    'area' => '',
),'',true,true);


return $settings;