<?php
/**
 * chimpx
 *
 * Copyright 2011 by Romain Trupault <romain@melting-media.com>
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
 * The base class for chimpx.
 *
 * @package chimpx
 */

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

use \DrewM\MailChimp\MailChimp;

class chimpx {
    public $mc = null;
    public $request;


    function __construct(modX &$modx, array $config = array()) {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption('chimpx.core_path', $config, $this->modx->getOption('core_path').'components/chimpx/');
        $assetsUrl = $this->modx->getOption('chimpx.assets_url', $config, $this->modx->getOption('assets_url').'components/chimpx/');
        $connectorUrl = $assetsUrl.'connector.php';

        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl.'css/',
            'jsUrl' => $assetsUrl.'js/',
            'imagesUrl' => $assetsUrl.'images/',

            'connectorUrl' => $assetsUrl.'connector.php',

            'corePath' => $corePath,
            'modelPath' => $corePath.'model/',
            'chunksPath' => $corePath.'elements/chunks/',
            'chunkSuffix' => '.chunk.tpl',
            'snippetsPath' => $corePath.'elements/snippets/',
            'processorsPath' => $corePath.'processors/',
            'templatesPath' => $corePath.'templates/',
        ), $config);

        // Let's load the MailChimp API
        if (!$this->mc) {
            // if (!$this->modx->loadClass('mailchimp.MCAPI', $this->config['modelPath'], true, true)) {
            //     $this->modx->log(modX::LOG_LEVEL_ERROR, '[chimpx] - unable to load MailChimp API');
            //     return false;
            // }
            //$mc = new MCAPI($this->modx->getOption('chimpx.apikey'), true);
            $mc = new MailChimp($this->modx->getOption('chimpx.apikey'));
            $this->mc = $mc;
        }

        @$this->modx->addPackage('chimpx', $this->config['modelPath'], 'modx_');
        $this->modx->lexicon->load('chimpx:default');
        return true;
    }

    /**
     * Initializes chimpx into different contexts.
     *
     * @access public
     * @param string $ctx The context to load. Defaults to web.
     */
    public function initialize($ctx = 'web') {

        switch ($ctx) {
            case 'mgr':
                if (!$this->modx->loadClass('chimpxControllerRequest', $this->config['modelPath'].'chimpx/request/', true, true)) {
                    return 'Could not load controller request handler.';
                }
                $this->request = new chimpxControllerRequest($this);
                return $this->request->handleRequest();
            break;
            case 'connector':
                if (!$this->modx->loadClass('chimpxConnectorRequest', $this->config['modelPath'].'chimpx/request/', true, true)) {
                    return 'Could not load connector request handler.';
                }
                $this->request = new chimpxConnectorRequest($this);
                return $this->request->handle();
            break;
            default:
                /* if you wanted to do any generic frontend stuff here.
                 * For example, if you have a lot of snippets but common code
                 * in them all at the beginning, you could put it here and just
                 * call $chimpx->initialize($modx->context->get('key'));
                 * which would run this.
                 */
            break;
        }
    }

    /**
     * Gets a Chunk and caches it; also falls back to file-based templates
     * for easier debugging.
     *
     * @access public
     * @param string $name The name of the Chunk
     * @param array $properties The properties for the Chunk
     * @return string The processed content of the Chunk
     */
    public function getChunk($name, array $properties = array()) {
        $chunk = null;
        if (!isset($this->chunks[$name])) {
            $chunk = $this->modx->getObject('modChunk', array('name' => $name), true);
            if (empty($chunk)) {
                $chunk = $this->_getTplChunk($name, $this->config['chunkSuffix']);
                if ($chunk == false) return false;
            }
            $this->chunks[$name] = $chunk->getContent();
        } else {
            $o = $this->chunks[$name];
            $chunk = $this->modx->newObject('modChunk');
            $chunk->setContent($o);
        }
        $chunk->setCacheable(false);
        return $chunk->process($properties);
    }

    /**
     * Returns a modChunk object from a template file.
     *
     * @access private
     * @param string $name The name of the Chunk. Will parse to name.chunk.tpl by default.
     * @param string $suffix The suffix to add to the chunk filename.
     * @return modChunk/boolean Returns the modChunk object if found, otherwise
     * false.
     */
    private function _getTplChunk($name, $suffix = '.chunk.tpl') {
        /** @var $chunk modChunk */
        $chunk = false;
        $f = $this->config['chunksPath'].strtolower($name).$suffix;
        if (file_exists($f)) {
            $o = file_get_contents($f);
            $chunk = $this->modx->newObject('modChunk');
            $chunk->set('name', $name);
            $chunk->setContent($o);
        }
        return $chunk;
    }

    /**
     * Retrieve a list of MailChimp campaigns
     * http://apidocs.mailchimp.com/1.3/campaigns.func.php
     *
     * @param array $filters
     * @param boolean $start
     * @param boolean $limit
     * @return array The campaigns list
     */
    public function getCampaigns($filters = array(), $start = false, $limit = false) {
        $params = array('filters'=>$filters, 'offset'=>$start, 'count'=>$limit);
        $campaigns = $this->mc->get('campaigns', $params);
        return $campaigns;
    }

    public function getCampaign($cid) {
        $campaign = $this->mc->get('campaigns/' . $cid);
        //TODO: Error checking
        // if ($campaign->errorCode){
        //     $msg = $modx->lexicon('chimpx.error_info', array(
        //         'number' => $api->errorCode,
        //         'message' => $api->errorMessage,
        //     ));
        //     return $modx->error->failure($msg);
        return $campaign;
    }

    public function getCampaignContent($cid) {
        $content = $this->mc->get('campaigns/' . $cid . '/content');
        //TODO: Error checking
        return $content;
    }

    public function displayCampaign ($campaign) {
        $record = [];

        $record['id'] = $campaign['id'];

        $record['subject'] = $campaign['settings']['subject_line'];
        $record['title'] = $campaign['settings']['title'];

        $record['list_from_name'] = $campaign['settings']['from_name'];
        $record['list_from_email'] = $campaign['settings']['reply_to'];

        $record['campaign_type'] = $campaign['type'];

        $record['list_id'] = $campaign['recipients']['list_id'];


        return $record;
    }

    /**
     * Prepares the data to be displayed in the manager page
     *
     * @param array $campaigns An of MailChimp campaigns
     * @return array The list
     */
    public function displayCampaigns($campaigns = array()) {
        $list = array();

        foreach ($campaigns['campaigns'] as $campaign) {

            $campaign['listname'] = $campaign['recipients']['list_name'];
            $campaign['title'] = $campaign['settings']['title'];
            $campaign['subject'] = $campaign['settings']['subject_line'];
            $campaign['status'] = $this->modx->lexicon('chimpx.campaign_status_'.$campaign['status']);
            $list[] = $campaign;
        }

        $listname = array_column($list, 'listname');
        $create_time = array_column($list, 'create_time');

        array_multisort($listname, SORT_ASC, $create_time, SORT_DESC, $list);

        return $list;
    }

    public function ping () {
        $response = $this->mc->get('ping');
        return $response['health_status'];
    }

    /**
     * Deletes the given campaign
     * http://apidocs.mailchimp.com/1.3/campaigndelete.func.php
     *
     * @param string $id The campaign ID
     */
    public function campaignDelete($id) {
        // @todo: ACLs (is user allowed to delete a campaign)
        $this->mc->delete('campaigns/' . $id);
    }

    /**
     * Clones the given campaign
     * http://apidocs.mailchimp.com/1.3/campaignreplicate.func.php
     *
     * @param string $id The campaign ID
     */
    public function campaignReplicate($id) {
        $this->mc->post('campaigns/' . $id . '/actions/replicate');
    }

    /**
     * Sends the given campaign
     * http://apidocs.mailchimp.com/1.3/campaignsendnow.func.php
     *
     * @param string $id The campaign ID
     */
    public function campaignSend($id) {
        $this->mc->post('campaigns/' . $id . '/actions/send');
    }

    /**
     * Sends a campaign test to the given addresses
     * http://apidocs.mailchimp.com/1.3/campaignsendtest.func.php
     *
     * @param string $id The campaign ID
     * @param string $to A comma separated list of emails
     */
    public function campaignSendtest($id, $to) {
        $emails = array();

        $emailList = explode(',', $to);
        foreach ($emailList as $email) {
            $emails[] = trim($email);
        }

        $params = array('test_emails'=>$emails, 'send_type'=>'html');

        $this->mc->post('campaigns/' . $id . '/actions/test', $params, 30);
    }

    /**
     * Updates the given campaign data
     * http://apidocs.mailchimp.com/1.3/campaignupdate.func.php
     *
     * @param string $cid The campaign ID
     * @param array $data An array of fields => values
     */
    public function campaignUpdate($cid, array $data = array()) {
        $campaign = $this->mc->patch('campaigns/' . $cid, $data);

        if ($data['url']) {
            $content = [];
            $content['url'] = $this->modx->makeUrl($data['url'],'','','abs');
            $this->mc->put('campaigns/' . $cid . '/content', $content);
        }
    }

    /**
     * Creates a new campaign
     * http://apidocs.mailchimp.com/1.3/campaigncreate.func.php
     *
     * @param array $data The campaign data
     */
    public function campaignCreate(array $data = array()) {

        $type = 'regular';

        // $options = array();
        // $content = array();
        // $segmentOptions = null;
        // $typeOptions = null;
        //$this->mc->campaignCreate($type, $options, $content, $segmentOptions, $typeOptions);

        $settings = [];

        $settings['subject_line'] = $data['subject'];
        $settings['title'] = $data['title'];
        $settings['from_name'] = $data['from_name'];
        $settings['reply_to'] = $data['from_email'];

        $recipients = [];
        $recipients['list_id'] = $data['list_select'];

        $params = [];
        $params['type'] = $type;
        $params['recipients'] = $recipients;
        $params['settings'] = $settings;
        
        $campaign = $this->mc->post('campaigns', $params);

        $cid = $campaign['id'];

        $content = [];
        $content['url'] = $this->modx->makeUrl($data['url'],'','','abs');

        $this->mc->put('campaigns/' . $cid . '/content', $content);

    }

    /**
     * Returns an array of lists attached to the MailChimp account
     * http://apidocs.mailchimp.com/1.3/lists.func.php
     *
     * @param array $filters An array of options to filter the lists
     * @param null||int $start
     * @param null||int $limit
     * @return array The list(s) details
     */
    public function getLists(array $filters = array(), $start = null, $limit = null) {
        // $limit = $limit > 100 ? 100 : $limit;

        $params = array('offset'=>$offset,'count'=>$limit);
        $lists = $this->mc->get('lists', $params);

        return $lists;
    }

    /**
     * Prepares the lists data to be used in the MODX manager
     *
     * @param array $data The list(s) data from MailChimp API
     * @param boolean $mergeTags Whether or not to include the list merge tags
     * @param boolean $location Whether or not to include list's subscribers locations
     * @return array The list(s) details
     */
    public function displayLists(array $data = array(), $mergeTags = false, $location = false) {
        $output = array();
        foreach ($data['lists'] as $listData) {
            $stats = $listData['stats'];
            unset ($listData['stats']);
            // Threat the stats data (prefixed with "stats-")
            foreach ($stats as $key => $value) {
                $listData['stats-'. $key] = $value;
            }
            // Threat the merge tags
            if ($mergeTags) {
                $listData['mergevars'] = $this->listMergeVars($listData['id']);
            }
            // Threat subscribers locations
            if ($location) {
                $listData['locations'] = $this->listLocations($listData['id']);
            }

            $listData['default_from_name'] = $listData['campaign_defaults']['from_name'];
            $listData['default_from_email'] = $listData['campaign_defaults']['from_email'];

            $output[] = $listData;
        }
        return $output;
    }

    /**
     * Returns the name of the given MailChimp list ID
     *
     * @param string $id The list ID
     * @return string The list name
     */
    public function getListName($id) {
        $data = $this->getLists(array('list_id' => $id));
        return $data['data'][0]['name'];
    }

    /**
     * Returns a list of merge tags for a given MailChimp list
     * http://apidocs.mailchimp.com/api/1.3/listmergevars.func.php
     *
     * @param string $id The list ID
     * @return array The merge tags list
     */
    public function listMergeVars($id) {
        $merge = $this->mc->listMergeVars($id);
        $output = array();
        foreach ($merge as $tag) {
            /*if ($tag['helptext'] === null) {
                // @todo: i18n
                $tag['helptext'] = 'not defined';
            }*/

            if ($tag['req']) {
                $tag['required'] = $this->modx->lexicon('yes');
            } else {
                $tag['required'] = $this->modx->lexicon('no');
            }
            $output[] = $tag;
        }

        return $output;
    }

    /**
     * Creates a new merge to for a given MailChimp list
     * http://apidocs.mailchimp.com/api/1.3/listmergevaradd.func.php
     *
     * @param array $data The merge tag data
     * @return boolean Whether of not the action went fine
     */
    public function listMergeVarAdd(array $data = array()) {
        $id = $data['id'];
        $tag = $data['tag'];
        $name = $data['name'];
        $options = $data['options'];
        $response = $this->mc->listMergeVarAdd($id, $tag, $name, $options);
        return $response;
    }

    /**
     * Returns a list of members of the given MailChimp list
     * http://apidocs.mailchimp.com/api/1.3/listmembers.func.php
     *
     * @param string $id The list ID to grab members from
     * @param array $params An array of options to filter the members
     * @param null||int $start
     * @param null||int $limit
     * @return array The list of members found
     */
    public function listMembers($id, array $params = array(), $start = null, $limit = null) {
        $status = $params['status'];
        $since = $params['since'];
        $subscribers = $this->mc->listMembers($id, $status, $since, $start, $limit);
        return $subscribers;
    }

    /**
     * Prepares the members list to be used in the MODX manager
     *
     * @param array $data
     * @return array The list of members
     */
    public function displayMembers(array $data) {
        $output = array();
        foreach ($data as $subscriber) {
            $inDb = $this->isModUser($subscriber['email']);
            if ($inDb) {
                $userData = $this->isModUser($subscriber['email'], true);
                $subscriber['moduser'] = $userData['moduser'];
                $subscriber['moduserid'] = $userData['moduserid'];
            } else {
                // @todo: i18n
                $subscriber['moduser'] = '';
            }
            $output[] = $subscriber;
        }
        return $output;
    }

    /**
     * Checks if a modUser is found using the same email address
     *
     * @param string $mail The user email address to look for
     * @param boolean $return Whether or not to return the modUser username if a match if found
     * @return boolean|mixed Whether true/false if a matching modUser is found, or the user username if found
     */
    public function isModUser($mail, $return = false) {
        /** @var $profile modUserProfile */
        $profile = $this->modx->getObject('modUserProfile', array('email' => $mail));
        if ($profile) {
            if ($return) {
                /** @var $user modUser */
                $user = $profile->getOne('User');
                $data['moduser'] = $user->get('username');
                $data['moduserid'] = $user->get('id');
                return $data;
            }
            return true;
        }
        return false;
    }

    /**
     * Returns a list of members, & their info, for the specified MailChimp list
     * http://apidocs.mailchimp.com/api/1.3/listmemberinfo.func.php
     *
     * @param string $list The list ID from which grabbing the member's details
     * @param string $mail The member email address
     * @return array The members details
     */
    public function listMemberInfo($list, $mail) {
        $members = $this->mc->listMemberInfo($list, $mail);
        return $members;
    }

    /**
     * Prepares the member(s) infos to be used in the MODX manager
     *
     * @param array $data The member(s) info retrieved from MailChimp API
     * @return array The member(s) info
     */
    public function displayMemberInfo(array $data) {
        $output = array();
        foreach ($data['data'] as $member) {
            if ($this->isModUser($member['email'])) {
                $user = $this->isModUser($member['email'], true);
                $member['moduser'] = $user['moduser'];
                $member['moduserid'] = $user['moduserid'];
            }
            $output[] = $member;
        }
        return $output;
    }

    /**
     * Retrieves a list of subscribers locations for the given MailChimp list
     * http://apidocs.mailchimp.com/api/1.3/listlocations.func.php
     *
     * @param string $id The list ID to retrieve subscribers' locations from
     * @return array The locations
     */
    public function listLocations($id) {
        $locations = $this->mc->listLocations($id);
        return $locations;
    }

    public function getResources () {

        $templates = $this->modx->getOption('chimpx.templates', '', '');
        if ($templates) $templates = explode(',', $templates);

        $query = $this->modx->newQuery('modDocument');
        $query->where(array('published'=>1));
        $query->where(array('deleted:!='=>1));
        if ($templates) {
            $query->where(array('template:IN'=>$templates));
        }
        $query->sortby('pagetitle', 'ASC');

        $items = $this->modx->getCollection('modDocument', $query);

        return $items;
    }

    /**
     * Checks if there is any error coming from the MailChimp API
     *
     * @return boolean
     */
    public function isError() {
        if ($this->mc->success()){
            return false;
        }
        return true;
    }

    /**
     * Returns error message from MailChimp API
     * http://apidocs.mailchimp.com/api/1.3/exceptions.field.php
     *
     * @return array|string The error message
     */
    public function getError() {
        if ($this->isError()){
            $error = $this->mc->getLastError();
            $msg = $this->modx->lexicon('chimpx.error_info', array(
                'number' => '',
                'message' => $error,
            ));

            return $this->modx->error->failure($msg);
        }
    }
}