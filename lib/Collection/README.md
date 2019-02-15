Collections
=================

These collections are traits implementing composed versions of Doctrine's ArrayCollection. The collection traits implement the methods required to implement the following interfaces:
* *Countable*
* *IteratorAggregate*
* *ArrayAccess*
* *Selectable* (from Doctrine Collections)

These collections implement a private, internal ArrayCollection's public interface on the composing class, turning any class into a Collection class. Four classes make up the collection traits:
* *ArrayCollectionInterface* must be implemented on any class using a collection trait.
* *ComposedCollectionTrait* is used to re-implement a composed ArrayCollection's methods on a class.
* *GraphCollectionTrait* introduces add and remove workflow to the the standard *ComposedCollectionTrait*, in order to ensure the key for an element matches the element's name property and to update the element's parent property.
* *CollectionCommonTrait* is used internally by *ComposedCollectionTrait* and *ComposedCollectionTrait* to implement most ArrayCollection methods.

The Doctrine Collections library is required. symfony/property-access is used heavily within *GraphCollectionTrait*.

The collection traits add syntactic sugar to the enclosing class, allowing it to be accessed through any of the standard array interfaces. In addition, hooking into the accessor chain (get/add/remove) is simplified.

There are concrete reference implementations for both traits:
* *ComposedCollection* is a concrete implementation of *ComposedCollectionTrait*.
* *GraphCollection* is a concrete implementation of *GraphCollectionTrait*.

*ClassnameMetadata* is an internal implementation detail of *GraphCollectionTrait*, containing the rules used to access the name and parent property of a child element.

## Usage
Generally, when you have a class containing a collection, it gets implemented similar to this:

    <?php

    namespace ...;

    use Doctrine\Common\Collections\ArrayCollection;

    class MagicCardCollection
    {
      private $children;

      public function __construct(array $elements = [])
      {
        $this->children = new ArrayCollection($elements);
      }

      /**
       * Standard collection accessors follow
       */

      public function getChildren()
      {
        return $this->children;
      }

      public function setChild($key, $element)
      {
        $this->children->set($key, $element);
      }

      public function addChild($element)
      {
        $this->children->add($element);
      }

      public function removeKey($key)
      {
        this->children->remove($key);
      }

      public function removeElement($element)
      {
        $this->children->removeElement($element);
      }
    }

### *CollectionTrait*
When implementing the collection trait, the standard collection accessors are not required. The enclosing class will be accessed via well known array and collection access interfaces, instead.

    <?php

    namespace ...;

    use JBJ\ComposedCollections\Collection\ArrayCollectionInterface;
    use JBJ\ComposedCollections\Collection\CollectionTrait;

    class sunSpotCollection implements ArrayCollectionInterface
    {
      use CollectionTrait;

      public function __construct(array $sunSpots = [])
      {
        // boiler-plate initialization code
        if (!empty($sunSpots)) {
          // The condition delays children creation until first use for empty array
          $this->setChildren($sunSpots);
        }
      }
    }

Now sunSpotCollection is accessed like any array or collection:

    <?php

    public function addSunSpots(array $sunSpots) {
      $collection = new sunSpotCollection();

      foreach ($collection as $key => $element) {
        $collection[] = $element;
      }
    }

    etc...

*ComposedCollectionTrait* is fully compatible with Doctrine's ArrayCollection. Check out the Doctrine/Collections documentation for usage of any class enclosing this trait.

### *GraphCollectionTrait*

The **GraphCollectionTrait** is an example of hooking into the collection's accessor chain to perform filtering and manipulation of the element's added to it.

The primary goal of the trait is a simple implementation of graph accessor dynamics.

The add/set hook follows the following workflow:
* Assert $element is valid. An element is valid if it is an *object* that has name and parent accessors.
* Get $elementName from the element.
* Set $elementName => $element.
* Set $elementParent to $this.

The remove hook follows the following workflow:

* Elements in the collection are assumed to be valid.
* Remove $element.
* Set $elementParent to null.

A simple leaf node implementation would be similar to this:

    <?php

    namespace ...;

    use JBJ\ComposedCollections\Collection\CollectionTrait;

    class Butterfly
    {
      /**
       * This is a leaf node in the graph.
       * Implementing the CollectionTrait doesn't change this and is not
       * required for a leaf node. The only requirement of a leaf node is
       * supporting a name and parent property.
       * In order to make this a branch node in the graph, it would need
       * to implement GraphCollectionTrait.
       */
      use CollectionTrait;

      private $name;
      private $parent;
      private $details;

      public function __construct(string $name, array $details = [])
      {
        $this->name = $name;
        if (!empty($details)) {
          $this->setChildren($details);
        }
      }

      public function getButterflyName()
      {
        return $this->name;
      }

      public function getButterflyCollection()
      {
        return $this->parent;
      }

      public function setButterflyCollection(ButterflyCollection $parent)
      {
        $this->parent = $parent;
      }
    }

And the collection class:

    <?php

    namespace ...;

    use JBJ\ComposedCollections\Collection\ArrayCollectionInterface;
    use JBJ\ComposedCollections\Collection\GraphCollectionTrait;
    use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

    class ButterflyCollection implements ArrayCollectionInterface
    {
      use GraphCollectionTrait;

      private $name;

      public function __construct(string $name, array $butterflies = [], PropertyAccessInterface $propertyAccessor = null)
      {
        $rules = [
          'name' => [
            'butterflyName', // this is a default property name
            'isDisabled' => false,
            'isValid' => true,
          ],
          'parent' => [
            'butterflyCollection', //this is a default property name
            'isDisabled' => false,
            'isValid' => true,
          ]
        ];
        $this->initializeTrait($name, $butterflies, $rules, $propertyAccessor);
      }
    }

And using this graph member implementation:

    <?php

    namespace ...;

    etc...

    // Create a butterfly collection
    $collection = new ButterflyCollection('collection-name');

    // Create some butterfly details
    $butterflies = [
      new Butterfly('elite lightening intensive attack butterfly :)'),
      new Butterfly('friendly magnetic worm'),
      new Butterfly('transforming dust butterfly'),
    ];

    foreach ($butterflies as $butterfly) {
      $this->collection[] = $butterfly;
    }

Each butterfly parent property will be set to the $collection when added. When removed, the parent will be reset to null.

todo: document property rules
