<?php namespace inject;



/**
 * Non-generic, BC retainining injector with providers bound for:
 * <ul>
 *   <li>rdbms.DBConnection - named, via rdbms.ConnectionManager</li>
 *   <li>util.log.LogCategory, named, via util.log.Logger</li>
 *   <li>util.Properties - named, via util.PropertyManager</li>
 * </ul>
 *
 * @see   xp://xp.command.Runner
 */
class XPInjector extends Injector {

  /**
   * Create bindings
   *
   */
  public function __construct() {
    if (class_exists('rdbms\DBConnection')) {   // TODO: Check for module? Inject via module RFC? Class path search?
      $this->addBinding('rdbms.DBConnection', newinstance('inject.Provider', array(), '{
        public function get($name= NULL) {
          return \rdbms\ConnectionManager::getInstance()->getByHost($name, 0);
        }
      }'));
    }
    $this->addBinding('util.log.LogCategory', newinstance('inject.Provider', array(), '{
      public function get($name= NULL) {
        return \util\log\Logger::getInstance()->getCategory($name);
      }
    }'));
    $this->addBinding('util.Properties', newinstance('inject.Provider', array(), '{
      public function get($name= NULL) {
        $p= \util\PropertyManager::getInstance()->getProperties($name);

        // If a PropertyAccess is retrieved which is not a util.Properties,
        // then, for BC sake, convert it into a util.Properties
        if ($p instanceof \util\PropertyAccess && !$p instanceof \util\Properties) {
          $convert= \util\Properties::fromString("");

          $section= $p->getFirstSection();
          while ($section) {
            // HACK: Properties::writeSection() would first attempts to
            // read the whole file, we cannot make use of it.
            $convert->_data[$section]= $p->readSection($section);
            $section= $p->getNextSection();
          }

          return $convert;
        } else {
          return $p;
        }
      }
    }'));
  }
}
