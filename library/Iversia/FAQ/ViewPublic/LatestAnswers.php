<?php

class Iversia_FAQ_ViewPublic_LatestAnswers extends XenForo_ViewPublic_Base
{
	public function renderRss()
	{
		$options = XenForo_Application::get('options');
		$title = ($options->boardTitle ? $options->boardTitle : XenForo_Link::buildPublicLink('canonical:index'));
		$description = ($options->boardDescription ? $options->boardDescription : $title);

		$buggyXmlNamespace = (defined('LIBXML_DOTTED_VERSION') && LIBXML_DOTTED_VERSION == '2.6.24');

		$feed = new Zend_Feed_Writer_Feed();
		$feed->setEncoding('utf-8');
		$feed->setTitle($title);
		$feed->setDescription($description);
		$feed->setLink(XenForo_Link::buildPublicLink('canonical:index'));
		if (!$buggyXmlNamespace)
		{
			$feed->setFeedLink(XenForo_Link::buildPublicLink('canonical:faq/latest-answers.rss'), 'rss');
		}
		$feed->setDateModified(XenForo_Application::$time);
		$feed->setLastBuildDate(XenForo_Application::$time);
		$feed->setGenerator($title);

		foreach ($this->_params['questions'] AS $question)
		{
			$entry = $feed->createEntry();
			$entry->setTitle($question['question']);
			$entry->setLink(XenForo_Link::buildPublicLink('canonical:faq', $question));
			$entry->setDateCreated(new Zend_Date($question['submit_date'], Zend_Date::TIMESTAMP));
			$entry->setDateModified(new Zend_Date($question['submit_date'], Zend_Date::TIMESTAMP));

			if (!$buggyXmlNamespace)
			{
				/*
				$entry->addAuthor(array(
					'name' => $thread['username'],
					'uri' => XenForo_Link::buildPublicLink('canonical:members', $thread)
				));
				if ($thread['reply_count'])
				{
					$entry->setCommentCount($thread['reply_count']);
				}
				*/
			}

			$feed->addEntry($entry);
		}

		return $feed->export('rss');
	}
}