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
 * Update a MailChimp campaign
 *
 * @var modX $modx
 * @var chimpx $chimpx
 * @package chimpx
 * @subpackage processors
 */
$chimpx =& $modx->chimpx;

if (empty($scriptProperties['id'])) {
    $msg = $modx->lexicon('chimpx.campaign_err_nf');
    $modx->log(modX::LOG_LEVEL_INFO,$msg);
    return $modx->error->failure($msg);
}
$cid = $scriptProperties['id'];

$data= [];

$settings = [];
$settings['subject_line'] = $modx->getOption('subject', $_POST, '');
$settings['title'] = $modx->getOption('title', $_POST, '');
$settings['from_name'] = $modx->getOption('from_name', $_POST, '');
$settings['from_email'] = $modx->getOption('from_email', $_POST, '');

$recipients = [];
$recipients['list_id'] = $modx->getOption('list_select', $_POST);

$data['settings'] = $settings;
$data['recipients'] = $recipients;

$data['url'] = $modx->getOption('url', $_POST);

$chimpx->campaignUpdate($cid, $data);

if ($chimpx->isError()){
    return $chimpx->getError();
}
$msg = 'Campaign ID '. $cid .' updated.';
//$msg = $modx->lexicon('chimpx.campaign_updated', array('id' => $cid));
return $modx->error->success('', $msg);