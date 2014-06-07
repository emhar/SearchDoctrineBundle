# Search Doctrine Bundle

## What is SearchDoctrineBundle ?

Simple way to create a search service in tiny web sites.
It works without search engine, just with Doctrine ORM.
Describe your result with a simple PHP class, bind it with annotations to your Doctrine entities.

Result objects are ordered by score. Score is calculated weight of requested words.

This Bundle provide a service that generate a search Form, search results and result count.

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

class Ressource extends AbstractItem
{

	//...

	/**
	 * Construct Ressource
	 *
	 * @Hit(name="id", rankFactor=0, sortable=false, mapping={
	 * 	"MyBundle:Album"={"attributeName":"id", "type":"integer"},
	 * 	"MyBundle:Artist"={"attributeName":"id", "type":"integer"}
	 * })
	 * @Hit(name="name", rankFactor=3, sortable=false, mapping={
	 * 	"MyBundle:Album"={"attributeName":"name", "type":"string"},
	 * 	"MyBundle:Artist"={"attributeName":"name", "type":"string"}
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

$searchService = $this->get('emhar_search_doctrine.search_service');
/* @var $searchService \Emhar\SearchDoctrineBundle\Services\SearchService */
$form = $searchService->getForm(Ressource::getViewName(), $this->generateUrl('...'));
$form->handleRequest($this->getRequest());
if ($form->isValid())
{
	$ressources = $searchService->getResults(Ressource::getViewName(), $form, $page);
	$pageCount = $searchService->getPageCount(Ressource::getViewName(), $form);
}
//...
```

> You must provide an action URI to `getForm` method

### Hit annotation

```php
/**
 * @Hit(name="id", rankFactor=0, sortable=false, mapping={
 * 	"MyBundle:Album"={"attributeName":"id", "type":"integer"},
 * 	"MyBundle:Artist"={"attributeName":"id", "type":"integer"}
 * })
 */
```

- **name**:			constructor argument `string` `required`
- **rankFactor**:		hit factor in score `int` `default 1`
- **sortable**:		boolean, determine if hit is sortable `bool` `default false`, currently not supported
- **mapping index**:	entity name `string`
- **attributeName**:	entity attribute name `string` `required`
- **type**:			entity attribute type, must be a valid doctrine type `string` `required`

### Several types, string conversion

If a hit has several types, returns depending on type :
- result casted as string
- empty string.

```php
/**
 * @Hit(name="id", rankFactor=0, sortable=false, mapping={
 * 	"MyBundle:Album"={"attributeName":"id", "type":"integer"},
 * 	"MyBundle:Artist"={"attributeName":"id", "type":"string"}
 * })
 */
```

### Omit some entities

You can omit some entities in the hit definitions, returns null value.

```php
/**
 * @Hit(name="id", rankFactor=0, sortable=false, mapping={
 * 	"MyBundle:Album"={"attributeName":"id", "type":"integer"},
 * 	"MyBundle:Artist"={"attributeName":"id", "type":"integer"}
 * })
 * @Hit(name="name", rankFactor=3, sortable=false, mapping={
 * 	"MyBundle:Album"={"attributeName":"name", "type":"string"}
 * })
 */
```

### Chain getter, <n>ToOne relation

You can chain getters for <n>ToOne relations, implode attribute names by points.

```php
/**
 * @Hit(name="ownerName", rankFactor=2, sortable=false, mapping={
 * 	"MyBundle:Album"={"attributeName":"artist.name", "type":"string"},
 * 	"MyBundle:Track"={"attributeName":"album.name", "type":"string"}
 * })
 */
```

> **Waring**: Chained getters duplicates results in <n>ToMany relations.

### Get score and entity name

If "score" or "type" are constructor argument, argument get the score and entity name value.
You can call parent constructor and getters

```php
//...
public function __construct($id, $name, $score, $type)
{
	parent::__construct($type, $score);
	//...
}
//...
```

or

```php
//...
public function __construct($id, $name)
{
	//...
}
//...
```