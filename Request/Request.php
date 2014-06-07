<?php

namespace Emhar\SearchDoctrineBundle\Request;

/**
 * Request.
 *
 * @author Emhar
 */
class Request
{

	/**
	 * Text which must be search
	 *
	 * @var string
	 */
	protected $searchText;

	/**
	 * Limit of results
	 *
	 * @var int
	 */
	protected $limit;

	/**
	 * Get text which must be search
	 *
	 * @return string
	 */
	public function getSearchText()
	{
		return $this->searchText;
	}

	/**
	 * Get limit of results
	 *
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Set text which must be search
	 *
	 * @param string $searchText
	 * @return Request
	 */
	public function setSearchText($searchText)
	{
		$this->searchText = $searchText;
		return $this;
	}

	/**
	 * Set limit of results
	 * 
	 * @param int $limit
	 * @return Request
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

}
