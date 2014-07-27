# Search Doctrine Bundle

## What is SearchDoctrineBundle ?

Simple way to create a search service in tiny web sites.
It works without search engine, just with Doctrine ORM.
Describe your result with a simple PHP class, bind it with annotations to your Doctrine entities.

This Symfony2 Bundle provide a service that generate a search Form, search results and result count.
Result objects are ordered by score, calculated with requested word weights in hits.

[![Latest Stable Version](https://poser.pugx.org/emhar/search-doctrine-bundle/v/stable.svg)](https://packagist.org/packages/emhar/search-doctrine-bundle)
[![Total Downloads](https://poser.pugx.org/emhar/search-doctrine-bundle/downloads.svg)](https://packagist.org/packages/emhar/search-doctrine-bundle)
[![Latest Unstable Version](https://poser.pugx.org/emhar/search-doctrine-bundle/v/unstable.svg)](https://packagist.org/packages/emhar/search-doctrine-bundle)
[![License](https://poser.pugx.org/emhar/search-doctrine-bundle/license.svg)](https://packagist.org/packages/emhar/search-doctrine-bundle)
[![Build Status](https://travis-ci.org/emhar/SearchDoctrineBundle.svg?branch=master)](https://travis-ci.org/emhar/SearchDoctrineBundle)

## Installation

### Composer

Add the following dependencies to your projects composer.json file:

    "require": {
        # ..
        "emhar/search-doctrine-bundle": "dev-master"
        # ..
    }


You have to enable the bundle in your AppKernel.php :

```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        //...
			new Emhar\SearchDoctrineBundle\EmharSearchDoctrineBundle(),
		//...
    );
}
```

## Usage

### Describe a Search Item

```php

//...

use Emhar\SearchDoctrineBundle\SearchItem\AbstractItem;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\Hit;
use Emhar\SearchDoctrineBundle\Mapping\Annotation\ItemEntity;

/**
 * @ItemEntity(
 *	identifier="artist",
 *  label="My Artists",
 *  entityClass="MyBundle\Entity\Artist"
 * )
 * @ItemEntity(
 *	identifier="album",
 *  label="My Albums",
 *  entityClass="MyBundle\Entity\Album"
 * )
 */
class Resource extends AbstractItem
{

	//...

	/**
	 * Construct Resource
	 *
	 * @Hit(name="id", scoreFactor=0, sortable=false, label="ID", mapping={
	 * 	"album"="id",
	 * 	"artist"="id"
	 * })
	 * @Hit(name="name", scoreFactor=3, sortable=false, label="Name", mapping={
	 * 	"album""name",
	 * 	"artist""name"
	 * })
	 */
	public function __construct($id, $name, $score, $type)
	{
		parent::__construct($type, $score);
		//...
	}

	//...
}
```
### Call service to get form, results and page count

```php
//...
use ...\Resource;
//...
$searchService = $this->get('emhar_search_doctrine.search_service');
/* @var $searchService \Emhar\SearchDoctrineBundle\Services\SearchService */
$form = $searchService->getForm(Resource::getItemClass(), $this->generateUrl('...'));
$form->handleRequest($this->getRequest());
if ($form->isValid())
{
	$ressources = $searchService->getResults(Resource::getItemClass(), $form, $page);
	$pageCount = $searchService->getPageCount(Resource::getItemClass(), $form);
}
//...
```

> You must provide an action URI to `getForm` method

### SearchItem annotation

```php
/**
 * @ItemEntity(
 *	identifier="album",
 *  label="My Albums",
 *  entityClass="MyBundle\Entity\Album"
 * )
 */
```

- **identifier**:		an identifier of your choice	`string`	`required`
- **label**:		Entity label, will be used in form	`string`	`required`
- **entityClass**:		Entity class, must be a valid doctrine entity	`string`	`required`


### Hit annotation

```php
/**
 * @Hit(name="id", scoreFactor=0, sortable=false, mapping={
 * 	"album"="id",
 * 	"artist"="id"
 * })
 */
```

- **identifier**:			constructor parameter `string` `required`
- **scoreFactor**:		hit factor in score `int` `default 1`
- **sortable**:		boolean, determine if hit is sortable `bool` `default false`, currently not supported
- **mapping index**:	entity identifier from SearchItem annotation `string`
- **attributeName**:	entity attribute name `string` `required`

### Several types, string conversion

If a hit has several types depending on the entities, returns depending on type :
- result casted as string
- empty string.

```php
/**
 * @Hit(name="id", scoreFactor=0, sortable=false, mapping={
 * 	"album"="id",
 * 	"artist"="name"
 * })
 */
```

### Omit some entities

You can omit some entities in the hit definitions, returns null value.

```php
/**
 * @Hit(identifier="id", scoreFactor=0, sortable=false, mapping={
 * 	"album"="id",
 * 	"artist"="id"
 * })
 * @Hit(identifier="name", scoreFactor=3, sortable=false, mapping={
 * 	"album"="name"
 * })
 */
```

### Chain getter, **n**ToOne relation

You can chain getters for **n**ToOne relations, implode attribute names by points.

```php
/**
 * @Hit(name="ownerName", scoreFactor=2, sortable=false, mapping={
 * 	"album"="artist.name",
 * 	"track"="album.artist.name"
 * })
 */
```

> **Waring**: Chained getters duplicates results in **n**ToMany relations.

### Get score and entity identifier

If you name an item constructor parameter :
- **score**:	receive the item score, calculate with the character number that match with request text
- **type**:	receive the entity identifier, useful to recognize from where the result comes and maybe generate different URL...


```php
//...
/**
 * Construct Resource
 *
 * @Hit(name="id", scoreFactor=0, sortable=false, label="ID", mapping={
 * 	"album"="id",
 * 	"artist"="id"
 * })
 * @Hit(name="name", scoreFactor=3, sortable=false, label="Name", mapping={
 * 	"album""name",
 * 	"artist""name"
 * })
 */
public function __construct($id, $name, $score, $type)
{
	//...
}
//...
```

Or omit them if you do not need

```php
//...
/**
 * Construct Resource
 *
 * @Hit(name="id", scoreFactor=0, sortable=false, label="ID", mapping={
 * 	"album"="id",
 * 	"artist"="id"
 * })
 * @Hit(name="name", scoreFactor=3, sortable=false, label="Name", mapping={
 * 	"album""name",
 * 	"artist""name"
 * })
 */
public function __construct($id, $name)
{
	//...
}
//...
```