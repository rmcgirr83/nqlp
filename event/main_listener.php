<?php
/**
 *
 * No quote last post. An extension for the phpBB Forum Software package.
 * @copuright (c) 2020, RMcgirr83
 * @copyright (c) 2019, tojag
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace tojag\nqlp\event;

/**
 * @ignore
 */
use phpbb\language\language;
use phpbb\template\template;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Tojag no quote last post Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	* Constructor
	*
	* @param \phpbb\language\language 	$language	Language object
	* @param \phpbb\template\template 	$template	Template object
	* @return \tojag\nqlp\event\main_listener
	* @access public
	*/
	public function __construct(language $language, template $template)
	{
		$this->language = $language;
		$this->template = $template;
	}

	public static function getSubscribedEvents()
	{
		return array(
			'core.acp_extensions_run_action_after'	=>	'acp_extensions_run_action_after',
			'core.viewtopic_modify_post_row'	=> 'viewtopic_modify_post_row',
			'core.modify_posting_auth'			=> 'modify_posting_auth'
		);
	}

	/* Display additional metadate in extension details
	*
	* @param $event			event object
	* @param return null
	* @access public
	*/
	public function acp_extensions_run_action_after($event)
	{
		if ($event['ext_name'] == 'tojag/nqlp' && $event['action'] == 'details')
		{
			$this->language->add_lang('common', $event['ext_name']);
			$this->template->assign_var('S_BUY_ME_A_BEER_NQLP', true);
		}
	}

	/**
	 * Don't display quote button, if a post is the last post in a topic.
	 */
	public function viewtopic_modify_post_row($event)
	{
		$row = $event['row'];
		$topic_data = $event['topic_data'];
		$post_row = $event['post_row'];
		if ($row['post_id'] == $topic_data['topic_last_post_id'])
		{
			$post_row['U_QUOTE'] = '';
		}
		$event['post_row'] = $post_row;
	}

	/**
	 * Don't allow quoting of last post, users can be tricky little buggers
	 */
	public function modify_posting_auth($event)
	{
		$post_id = (int) $event['post_id'];
		$topic_last_post_id = !empty($event['post_data']['topic_last_post_id']) ? (int) $event['post_data']['topic_last_post_id'] : 0;

		if ($post_id == $topic_last_post_id && $event['mode'] == 'quote')
		{
			$this->language->add_lang('common', 'tojag/nqlp');
			trigger_error($this->language->lang('CANNOT_QUOTE_LAST_POST'));
		}
	}
}
