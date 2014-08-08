<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the software license agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.x-cart.com/license-agreement.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@x-cart.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not modify this file if you wish to upgrade X-Cart to newer versions
 * in the future. If you wish to customize X-Cart for your needs please
 * refer to http://www.x-cart.com/ for more information.
 *
 * @category  X-Cart 5
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Model\Repo;

/**
 * Module repository
 */
class Module extends \XLite\Model\Repo\ARepo
{
    /**
     * Allowable search params
     */
    const P_SUBSTRING        = 'substring';
    const P_TAG              = 'tag';
    const P_ORDER_BY         = 'orderBy';
    const P_LIMIT            = 'limit';
    const P_PRICE_FILTER     = 'priceFilter';
    const P_INSTALLED        = 'installed';
    const P_ISSYSTEM         = 'isSystem';
    const P_INACTIVE         = 'inactive';
    const P_CORE_VERSION     = 'coreVersion';
    const P_FROM_MARKETPLACE = 'fromMarketplace';
    const P_IS_LANDING       = 'isLanding';
    const P_MODULEIDS        = 'moduleIds';
    const P_EDITION          = 'edition';

    /**
     * Price criteria
     */
    const PRICE_FREE = 'free';
    const PRICE_PAID = 'paid';


    /**
     * Repository type
     *
     * @var string
     */
    protected $type = self::TYPE_INTERNAL;

    /**
     * Alternative record identifiers
     *
     * @var array
     */
    protected $alternativeIdentifier = array(
        array('author', 'name'),
    );


    // {{{ The Searchable interface

    /**
     * Common search
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $queryBuilder = $this->createQueryBuilder();

        $this->currentSearchCnd = $cnd;

        foreach ($this->currentSearchCnd as $key => $value) {
            $this->callSearchConditionHandler($value, $key, $queryBuilder);
        }

        $this->addGroupByCondition($queryBuilder);
        $result = $queryBuilder->getOnlyEntities();

        return $countOnly ? count($result) : $result;
    }

    /**
     * Call corresponded method to handle a search condition
     *
     * @param mixed                      $value        Condition data
     * @param string                     $key          Condition name
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     *
     * @return void
     */
    protected function callSearchConditionHandler($value, $key, \Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $this->{'prepareCnd' . ucfirst($key)}($queryBuilder, $value);

        } else {
            // TODO - add logging here
        }
    }

    /**
     * Check if param can be used for search
     *
     * @param string $param Name of param to check
     *
     * @return boolean
     */
    protected function isSearchParamHasHandler($param)
    {
        return in_array($param, $this->getHandlingSearchParams());
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
            static::P_SUBSTRING,
            static::P_TAG,
            static::P_ORDER_BY,
            static::P_LIMIT,
            static::P_PRICE_FILTER,
            static::P_INSTALLED,
            static::P_ISSYSTEM,
            static::P_INACTIVE,
            static::P_CORE_VERSION,
            static::P_FROM_MARKETPLACE,
            static::P_IS_LANDING,
            static::P_MODULEIDS,
            static::P_EDITION,
        );
    }

    /**
     * Return conditions parameters that are responsible for substring set of fields.
     *
     * @return array
     */
    protected function getSubstringSearchFields()
    {
        return array(
            $this->getRelevanceTitleField(),
            $this->getRelevanceTextField(),
        );
    }

    /**
     * Return title field name for relevance
     *
     * @return string
     */
    protected function getRelevanceTitleField()
    {
        return 'm.moduleName';
    }

    /**
     * Return text field name for relevance
     *
     * @return string
     */
    protected function getRelevanceTextField()
    {
        return 'm.description';
    }

    /**
     * Return search words for "All" and "Any" INCLUDING parameter
     *
     * @param string $value Search string
     *
     * @return void
     */
    protected function getSearchWords($value)
    {
        $value  = trim($value);
        $result = array();

        if (preg_match_all('/"([^"]+)"/', $value, $match)) {
            $result = $match[1];
            $value = str_replace($match[0], '', $value);
        }

        return array_merge((array) $result, array_map('trim', explode(' ', $value)));
    }

    /**
     * Prepare query builder to get modules list
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     *
     * @return void
     */
    protected function addGroupByCondition(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->addGroupBy('m.name')
            ->addGroupBy('m.author');
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param array                      $value        Condition data
     *
     * @return void
     */
    protected function prepareCndLimit(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value)
    {
        $queryBuilder->setFrameResults($value);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param string|null                $value        Condition data
     *
     * @return void
     */
    protected function prepareCndSubstring(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $searchWords = $this->getSearchWords($value);
        $searchPhrase = implode(' ', $searchWords);
        $cnd = new \Doctrine\ORM\Query\Expr\Orx();

        foreach ($this->getSubstringSearchFields() as $field) {
            foreach ($searchWords as $index => $word) {
                // Collect OR expressions
                $cnd->add($field . ' LIKE :word' . $index);
                $queryBuilder->setParameter('word' . $index, '%' . $word . '%');
            }
        }

        if ($searchPhrase) {
            $queryBuilder->addSelect(
                'relevance(\'' . $value . '\', ' . $this->getRelevanceTitleField() . ', ' . $this->getRelevanceTextField() . ') as relevance'
            );

            $orderBys = $queryBuilder->getDQLPart('orderBy');
            $queryBuilder->resetDQLPart('orderBy');
            $queryBuilder->addOrderBy('relevance', 'desc');
            foreach ($orderBys as $value) {
                $queryBuilder->add('orderBy', $value, true);
            }
        }

        $queryBuilder->andWhere($cnd);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param string|null                $value        Condition data
     *
     * @return void
     */
    protected function prepareCndTag(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder
            ->andWhere('m.tags LIKE :tag')
            ->setParameter('tag', "%\"" . $value . "\"%");
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param string|null                $value        Condition data
     *
     * @return void
     */
    protected function prepareCndEdition(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder
            ->andWhere('m.editions LIKE :edition')
            ->setParameter('edition', "%\"" . $value . "\"%");
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param string|null                $value        Condition data
     *
     * @return void
     */
    protected function prepareCndModuleIds(\Doctrine\ORM\QueryBuilder $qb, $value)
    {
        if (is_array($value) && count($value) > 0) {
            $keys = \XLite\Core\Database::buildInCondition($qb, $value);
            $qb->andWhere(
                $this->getMainAlias($qb) . '.' . $this->_class->identifier[0]
                . ' IN (' . implode(', ', $keys) . ')'
            );
        }
    }

    /**
     * prepareCndOrderBy
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder QueryBuilder instance
     * @param array                      $value        Order by info
     *
     * @return void
     */
    protected function prepareCndOrderBy(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value)
    {
        $values = is_array($value[0]) ? $value : array($value);

        foreach ($values as $val) {

            list($sort, $order) = $this->getSortOrderValue($val);

            if (!empty($sort)) {
                $queryBuilder->addOrderBy($sort, $order);
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param string                     $value        Condition
     *
     * @return void
     */
    protected function prepareCndPriceFilter(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if (self::PRICE_FREE === $value) {

            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('m.price', 0),
                    $queryBuilder->expr()->gt('m.xcnPlan', 0)
                )
            );

        } elseif (self::PRICE_PAID === $value) {
            $queryBuilder->andWhere($queryBuilder->expr()->gt('m.price', 0))
                ->andWhere($queryBuilder->expr()->lte('m.xcnPlan', 0));
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param boolean                    $value        Condition
     *
     * @return void
     */
    protected function prepareCndInstalled(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder
            ->andWhere('m.installed = :installed')
            ->setParameter('installed', $value);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param boolean                    $value        Condition
     *
     * @return void
     */
    protected function prepareCndIsSystem(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder
            ->andWhere('m.isSystem = :isSystem')
            ->setParameter('isSystem', $value);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     *
     * @return void
     */
    protected function prepareCndInactive(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->andWhere('m.enabled = :enabled')
            ->setParameter('enabled', false);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param string                     $value        Condition
     *
     * @return void
     */
    protected function prepareCndCoreVersion(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder
            ->andWhere('m.majorVersion = :majorVersion')
            ->setParameter('majorVersion', $value);
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param boolean                    $value        Condition
     *
     * @return void
     */
    protected function prepareCndFromMarketplace(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if ($value) {
            $queryBuilder
                ->andWhere('m.fromMarketplace = :fromMarketplace')
                ->setParameter('fromMarketplace', true);
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param boolean                    $value        Condition
     *
     * @return void
     */
    protected function prepareCndIsLanding(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        $queryBuilder
            ->andWhere('m.isLanding = :isLanding')
            ->setParameter('isLanding', $value);
    }

    // }}}

    // {{{ Markeplace-related routines

    /**
     * One time in session we update list of marketplace modules
     *
     * @param array $data Data received from marketplace
     *
     * @return void
     */
    public function updateMarketplaceModules(array $data)
    {
        // Get the list of non-installed modules from marketplace
        $queryBuilder = $this->createQueryBuilder();
        $this->prepareCndFromMarketplace($queryBuilder, true);
        $this->prepareCndInstalled($queryBuilder, false);

        $modules = $queryBuilder->getResult();

        // Update existing modules
        if (!empty($modules)) {
            foreach ($modules as $module) {
                $key = sprintf(
                    '%s_%s_%s',
                    $module->getAuthor(),
                    $module->getName(),
                    $module->getMajorVersion()
                );

                if (isset($data[$key])) {
                    $this->update($module, $data[$key], true);
                    unset($data[$key]);
                } else {
                    \XLite\Core\Database::getEM()->remove($module);
                }
            }
        }

        // Add new modules
        $this->insertInBatch($data, false);
    }

    // }}}

    // {{{ Version-related routines

    /**
     * Search for modules having an elder version
     *
     * @param \XLite\Model\Module $module Module to get info from
     *
     * @return \XLite\Model\Module
     */
    public function getModuleForUpdate(\XLite\Model\Module $module)
    {
        return $this->defineModuleForUpdateQuery($module)->getSingleResult();
    }

    /**
     * Search for modules having an elder version
     *
     * @param \XLite\Model\Module $module Module to get info from
     *
     * @return \XLite\Model\Module
     */
    public function getModuleFromMarketplace(\XLite\Model\Module $module)
    {
        return $this->defineModuleFromMarketplaceQuery($module)->getSingleResult();
    }

    /**
     * Search for installed module
     *
     * @param \XLite\Model\Module $module Module to get info from
     *
     * @return \XLite\Model\Module
     */
    public function getModuleInstalled(\XLite\Model\Module $module)
    {
        return $this->defineModuleInstalledQuery($module)->getSingleResult();
    }

    /**
     * Search module for upgrade
     *
     * @param \XLite\Model\Module $module Currently installed module
     *
     * @return \XLite\Model\Module
     */
    public function getModuleForUpgrade(\XLite\Model\Module $module)
    {
        return $this->defineModuleForUpgradeQuery($module)->getSingleResult();
    }

    /**
     * Query to search for modules having an elder version
     *
     * @param \XLite\Model\Module $module Module to get info from
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineModuleForUpdateQuery(\XLite\Model\Module $module)
    {
        $queryBuilder = $this->createQueryBuilder();
        $this->prepareCndSingleModuleSearch($queryBuilder, $module);

        $queryBuilder
            ->andWhere('m.majorVersion = :majorVersion')
            ->andWhere('m.minorVersion > :minorVersion')
            ->setParameter('majorVersion', $module->getMajorVersion())
            ->setParameter('minorVersion', $module->getMinorVersion());

        return $queryBuilder;
    }

    /**
     * Query to search for modules having an elder version
     *
     * @param \XLite\Model\Module $module Module to get info from
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineModuleFromMarketplaceQuery(\XLite\Model\Module $module)
    {
        $queryBuilder = $this->createQueryBuilder();
        $this->prepareCndSingleModuleSearch($queryBuilder, $module);

        $queryBuilder
            ->addOrderBy('m.majorVersion', 'ASC')
            ->addOrderBy('m.minorVersion', 'DESC');

        $this->prepareCndFromMarketplace($queryBuilder, true);

        return $queryBuilder;
    }

    /**
     * Query to search for modules having an elder version
     *
     * @param \XLite\Model\Module $module       Module to get info from
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineModuleForUpgradeQuery(\XLite\Model\Module $module)
    {
        $queryBuilder = $this->createQueryBuilder();
        $this->prepareCndSingleModuleSearch($queryBuilder, $module);

        $queryBuilder
            ->andWhere('m.majorVersion > :majorVersion')
            ->setParameter('majorVersion', $module->getMajorVersion())
            ->addOrderBy('m.minorVersion', 'DESC');

        return $queryBuilder;
    }

    /**
     * Query to search for installed modules
     *
     * @param \XLite\Model\Module $module Module to get info from
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineModuleInstalledQuery(\XLite\Model\Module $module)
    {
        $queryBuilder = $this->createQueryBuilder();
        $this->prepareCndSingleModuleSearch($queryBuilder, $module);
        $this->prepareCndInstalled($queryBuilder, true);

        return $queryBuilder;
    }

    /**
     * Helper to search module with the same name and author
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param \XLite\Model\Module        $module       Module to get info from
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function prepareCndSingleModuleSearch(\Doctrine\ORM\QueryBuilder $queryBuilder, \XLite\Model\Module $module)
    {
        $queryBuilder
            ->andWhere('m.name = :name')
            ->andWhere('m.author = :author')
            ->setParameter('name', $module->getName())
            ->setParameter('author', $module->getAuthor())
            ->setMaxResults(1);
    }

    // }}}

    // {{{ Search for dependencies

    /**
     * Search dependent modules by their class names
     *
     * @param array $classes List of class names
     *
     * @return void
     */
    public function getDependencyModules(array $classes)
    {
        $result = $this->getDependencyModulesCommon($classes, false);

        foreach ($result as $module) {
            unset($classes[$module->getActualName()]);
        }

        if (!empty($classes)) {
            $result = array_merge($result, $this->getDependencyModulesCommon($classes, true));
        }

        return $result;
    }

    /**
     * Common method to search modules by list of class names
     *
     * @param array   $classes         List of class names
     * @param boolean $fromMarketplace Flag OPTIONAL
     *
     * @return array
     */
    protected function getDependencyModulesCommon(array $classes, $fromMarketplace)
    {
        $cnds = array();
        $queryBuilder = $this->createQueryBuilder('m');

        foreach (array_keys($classes) as $idx => $class) {
            list($author, $name) = explode('\\', $class);

            $cnds[] = new \Doctrine\ORM\Query\Expr\Andx(array('m.name = :name' . $idx, 'm.author = :author' . $idx));
            $queryBuilder->setParameters(array('name' . $idx => $name, 'author' . $idx => $author));
        }

        return $queryBuilder
            ->andWhere(new \Doctrine\ORM\Query\Expr\Orx($cnds))
            ->andWhere('m.fromMarketplace = :fromMarketplace')
            ->setParameter('fromMarketplace', $fromMarketplace)
            ->addGroupBy('m.author', 'm.name')
            ->getResult();
    }

    // }}}

    /**
     * Add all enabled modules to ENABLED registry
     *
     * @return void
     */
    public function addEnabledModulesToRegistry()
    {
        foreach ($this->findBy(array('enabled' => true)) as $module) {

            \XLite\Core\Database::getInstance()->registerModuleToEnabledRegistry(
                $module->getActualName(),
                \Includes\Utils\ModulesManager::getModuleProtectedStructures($module->getAuthor(), $module->getName())
            );
        }
    }

    /**
     * Get registry HASH of enabled modules
     *
     * @return string
     */
    public function calculateEnabledModulesRegistryHash()
    {
        $hash = '';

        foreach ($this->findBy(array('enabled' => true)) as $module) {
            $hash .= $module->getActualName() . $module->getVersion();
        }

        return hash('md4', $hash);
    }

    /**
     * Returns the maximum downloads counter
     *
     * @return integer
     */
    public function getMaximumDownloads()
    {
        $module = $this->findBy(array('fromMarketplace' => true), array('downloads' => 'desc'), 1);

        return $module[0]->getDownloads();
    }

    /**
     * Return the page number of marketplace page for specific search
     *
     * @param string  $author
     * @param string  $module
     * @param integer $limit
     *
     * @return integer
     */
    public function getPageId($author, $module, $limit)
    {
        $moduleInfo = $this->findOneBy(array(
            'author'            => $author,
            'name'              => $module,
            'fromMarketplace'   => true,
        ));
        $page = 0;
        if ($moduleInfo) {
            $qb = $this
                ->createPureQueryBuilder('m')
                ->select('m.moduleID')
                ->where('m.fromMarketplace = :true AND m.isSystem = :false')
                ->setParameter('true', true)
                ->setParameter('false', false);

            $this->prepareCndOrderBy($qb, array(\XLite\View\ItemsList\Module\AModule::SORT_OPT_ALPHA, \XLite\View\ItemsList\AItemsList::SORT_ORDER_ASC));
            // The module list contains several records with all major versions available
            $this->addGroupByCondition($qb);

            $allModules = $qb->getArrayResult();

            $key        = array_search(array('moduleID' => $moduleInfo->getModuleID()), $allModules) + 1;
            $page       = intval($key / $limit);
            $remainder  = $key % $limit;
        }

        return (isset($remainder) && 0 === $remainder) ? $page - 1 : $page;
    }

    /**
     * Return the page number of "installed modules" page for specific search
     *
     * @param string  $author
     * @param string  $module
     * @param integer $limit
     *
     * @return integer
     */
    public function getModulePageId($author, $module, $limit)
    {
        $moduleInfo = $this->findOneBy(array(
            'author'            => $author,
            'name'              => $module,
            'fromMarketplace'   => false,
        ));
        $page = 0;
        if ($moduleInfo) {
            $qb = $this
                ->createPureQueryBuilder('m')
                ->select('m.moduleID')
                ->where('m.fromMarketplace = :false AND m.isSystem = :false')
                ->setParameter('false', false);

            $this->prepareCndOrderBy($qb, array(\XLite\View\ItemsList\Module\AModule::SORT_OPT_ALPHA, \XLite\View\ItemsList\AItemsList::SORT_ORDER_ASC));
            // The module list contains several records with all major versions available
            $this->addGroupByCondition($qb);

            $allModules = $qb->getArrayResult();

            $key        = array_search(array('moduleID' => $moduleInfo->getModuleID()), $allModules) + 1;
            $page       = intval($key / $limit);
            $remainder  = $key % $limit;
        }

        return (isset($remainder) && 0 === $remainder) ? $page - 1 : $page;
    }

    /**
     * Find one module by name
     *
     * @param string $name Module name
     *
     * @return \XLite\Model\Module
     */
    public function findOneByModuleName($name)
    {
        list($author, $module) = explode('\\', $name, 2);

        return $this->findOneBy(
            array(
                'author'          => $author,
                'name'            => $module,
                'fromMarketplace' => false,
            )
        );
    }

    /**
     * Check - module is eEnabled or not
     *
     * @param string $name Module name
     *
     * @return boolean
     */
    public function isModuleEnabled($name)
    {
        $module = $this->findOneByModuleName($name);

        return $module && $module->getEnabled();
    }

    /**
     * Marketplace modules list
     *
     * @param string $edition
     *
     * @return null|array
     */
    public function getNonFreeEditionModulesList($enabledFlag = null)
    {
        $cnd = new \XLite\Core\CommonCell;
        $cnd->{static::P_FROM_MARKETPLACE} = true;

        $result = $this->search($cnd);
        $freeEdition = \XLite\Core\Marketplace::getInstance()->getFreeLicenseEdition();

        foreach ($result as $key => $module) {
            $editions = $module->getEditions();

            if (empty($editions) || in_array($freeEdition, $editions)) {
                unset($result[$key]);
            } else {
                $installedModule = $this->findOneBy(array(
                    'name'              => $module->getName(),
                    'author'            => $module->getAuthor(),
                    'fromMarketplace'   => 0,
                ));

                if ($installedModule) {
                    $result[$key]->setEnabled($installedModule->getEnabled());
                }

                if ($enabledFlag && !$result[$key]->getEnabled()) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

    // {{{ getModuleState()

    /**
     * Find module state by module author/name values
     *
     * @param string $module Module author/name (string 'Author\\Name')
     *
     * @return boolean
     */
    public function getModuleState($module)
    {
        list($author, $name) = explode('\\', $module);

        $data = $this->defineGetModuleStateQuery($author, $name)->getArrayResult();

        if (0 < count($data)) {
            $result = $data[0]['enabled'];

        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Prepare query builder for getModuleState() method
     *
     * @param string $author Module author
     * @param string $name   Module name
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function defineGetModuleStateQuery($author, $name)
    {
        return $this->createPureQueryBuilder('m')
            ->select('m.enabled')
            ->where('m.author = :author')
            ->andWhere('m.name = :name')
            ->andWhere('m.installed = :true')
            ->orderBy('m.installed', 'DESC')
            ->addOrderBy('m.author', 'ASC')
            ->addOrderBy('m.name', 'ASC')
            ->setParameter('author', $author)
            ->setParameter('name', $name)
            ->setParameter('true', 1)
            ->setMaxResults(1);
    }

    // }}}
}
