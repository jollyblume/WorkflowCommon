Common Components
=================

## General status
The code base is stable and it's interfaces are not going to be changing. Unit tests are complete for most feature, but many tests remain to be written. Existing test provide a credible level of code quality for development use. I don't expect any bugs
in the current code base. Of course, everyone knows how foolish that notion is until test units are complete...

This README needs to be completed, as do code docblocks.

I do not intend to release a 1.0 version until these todo's are complete.

## Library overview
This component includes three sections:
* The *Collection* folder contains two collection traits based on **Doctrine/Common/Collections/ArrayCollection**.
  These collections implement a private, internal ArrayCollection's public interface on the composing class, turning any class into a Collection class.
* The *Exception* folder contains, among other classes, a FixMeException used during some development cycles. References to this Exception will be refactored out of the final code base.
* The *Validator* folder contains a validator from the awesome **Ramsey/Uuid** dev branch. These classes will be removed once they make it to **Ramsey/Uuid** master.

In addition to the libraries all ready mentioned, this component also uses symfony/property-access.

## Collection classes
The collection traits add syntactic sugar to the enclosing class, allowing it to be accessed through any of the standard array interfaces. In addition, hooking into the accessor chain (get/add/remove) is simplified.

There are two collection traits used by arbitrary classes to implement Collection and Selectable interfaces on themselves:
* **CollectionTrait:**
Using this trait implements the entire public interface  from **Doctrine/Common/Collections/ArrayCollection**, including:
  * ArrayAccess
  * Countable
  * IteratorAggregate
  * Doctrine/Common/Collections/Collection
  * Doctrine/Common/Collections/Selectable
* **GraphCollectionTrait**
  This trait introduces add and remove workflow to the the standard **CollectionTrait**, in order to ensure the key for an element matches the element's name property and to update the element's parent property.

In addition to the traits, there are four other important classes in the **JBJ\ComposedCollections\Collection** namespace:
* **ArrayCollectionInterface** must be implemented by and class using the collection traits. It pulls in the other interfaces mentioned previously.
* **ComposedCollection** is a concrete implementation using the **CollectionTrait**. It is as an example only.
* **GraphCollection** is a concrete implementation using the **GraphCollectionTrait**. It is as an example only.
* **ClassnameMetadata** is an internal implementation detail of the **GraphCollectionTrait**. It manages a list of property names used by child elements to get an element's name property and to get/set an element's parent property.

## Usage
### General private collection implementation
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

Accessing this class is straight forward:

    <?php

    namespace ...;

    class CardUser
    {
      private $magicCards;

      public function __construct(array $cards = [])
      {
        $this->magicCards = new MagicCardCollection($cards);
      }

      public function doSomething()
      {
        $cards = $this->MagicCardCollection;

        // Add a card
        $cards->addChild('awesome-card');

        $awesomeCards = $this->children->filter

        etc...
      }
    }

### *CollectionTrait* implementation
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

**CollectionTrait** is fully compatible with Doctrine's ArrayCollection. Check out the Doctrine/Collections documentation for usage of any class enclosing this trait.

### *GraphCollectionTrait* implementation

The **GraphCollectionTrait** is an example of hooking into the collection's accessor chain to perform filtering and manipulation of the element's added to it.

The primary goal of the trait is a simple implementation of graph accessor dynamics.

The add/set hook follows a simple workflow:

* The add and set accessor hooks filter elements, throwing an exception when an element has no valid rule defined. Filter rules are described later in this document.
* The element's parent property is set to $this.
* Finally, the element is set into the collection.

The remove hook follow an even simpler workflow:

* Elements in the collection are assumed to be valid.
* The element is removed from the collection.
* The element's parent property is set to null.

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
