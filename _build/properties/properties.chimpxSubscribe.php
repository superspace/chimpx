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
 * Properties for the chimpx snippet.
 *
 * @package chimpx
 * @subpackage build
 */
$properties = array(
    array(
        'name' => 'formTpl',
        'desc' => 'prop_chimpx.formTpl_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => 'formTpl',
        'lexicon' => 'chimpx:properties',
    ),
    array(
        'name' => 'listId',
        'desc' => 'prop_chimpx.listId_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'chimpx:properties',
    ),
    array(
        'name' => 'debug',
        'desc' => 'prop_chimpx.debug_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '0',
        'lexicon' => 'chimpx:properties',
    ),
    array(
        'name' => 'errorMsg',
        'desc' => 'prop_chimpx.errorMsg_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'chimpx:properties',
    ),
    array(
        'name' => 'successMsg',
        'desc' => 'prop_chimpx.successMsg_desc',
        'type' => 'textfield',
        'options' => '',
        'value' => '',
        'lexicon' => 'chimpx:properties',
    ),
);

return $properties;