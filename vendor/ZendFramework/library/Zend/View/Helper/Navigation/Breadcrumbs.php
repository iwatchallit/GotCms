<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\View\Helper\Navigation;

use Zend\Navigation\Container,
    Zend\Navigation\Page\AbstractPage,
    Zend\View,
    Zend\View\Exception;

/**
 * Helper for printing breadcrumbs
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Breadcrumbs extends AbstractHelper
{
    /**
     * Breadcrumbs separator string
     *
     * @var string
     */
    protected $separator = ' &gt; ';

    /**
     * The minimum depth a page must have to be included when rendering
     *
     * @var int
     */
    protected $minDepth = 1;

    /**
     * Whether last page in breadcrumb should be hyperlinked
     *
     * @var bool
     */
    protected $linkLast = false;

    /**
     * Partial view script to use for rendering menu
     *
     * @var string|array
     */
    protected $partial;

    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param  Container $container [optional] container to operate on
     * @return Breadcrumbs  fluent interface, returns self
     */
    public function __invoke(Container $container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    // Accessors:

    /**
     * Sets breadcrumb separator
     *
     * @param  string $separator separator string
     * @return Breadcrumbs fluent interface, returns self
     */
    public function setSeparator($separator)
    {
        if (is_string($separator)) {
            $this->separator = $separator;
        }

        return $this;
    }

    /**
     * Returns breadcrumb separator
     *
     * @return string  breadcrumb separator
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Sets whether last page in breadcrumbs should be hyperlinked
     *
     * @param  bool $linkLast whether last page should be hyperlinked
     * @return Breadcrumbs fluent interface, returns self
     */
    public function setLinkLast($linkLast)
    {
        $this->linkLast = (bool) $linkLast;
        return $this;
    }

    /**
     * Returns whether last page in breadcrumbs should be hyperlinked
     *
     * @return bool  whether last page in breadcrumbs should be hyperlinked
     */
    public function getLinkLast()
    {
        return $this->linkLast;
    }

    /**
     * Sets which partial view script to use for rendering menu
     *
     * @param  string|array $partial partial view script or null. If an array is
     *                               given, it is expected to contain two 
     *                               values; the partial view script to use, 
     *                               and the module where the script can be 
     *                               found.
     * @return Breadcrumbs fluent interface, returns self
     */
    public function setPartial($partial)
    {
        if (null === $partial || is_string($partial) || is_array($partial)) {
            $this->partial = $partial;
        }

        return $this;
    }

    /**
     * Returns partial view script to use for rendering menu
     *
     * @return string|array|null
     */
    public function getPartial()
    {
        return $this->partial;
    }

    // Render methods:

    /**
     * Renders breadcrumbs by chaining 'a' elements with the separator
     * registered in the helper
     *
     * @param  Container $container [optional] container to render. Default is
     *                              to render the container registered in the helper.
     * @return string               helper output
     */
    public function renderStraight(Container $container = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        // find deepest active
        if (!$active = $this->findActive($container)) {
            return '';
        }

        $active = $active['page'];

        // put the deepest active page last in breadcrumbs
        if ($this->getLinkLast()) {
            $html = $this->htmlify($active);
        } else {
            $html = $active->getLabel();
            if ($this->getUseTranslator() && $t = $this->getTranslator()) {
                $html = $t->translate($html);
            }
            $html = $this->view->vars()->escape($html);
        }

        // walk back to root
        while ($parent = $active->getParent()) {
            if ($parent instanceof AbstractPage) {
                // prepend crumb to html
                $html = $this->htmlify($parent)
                      . $this->getSeparator()
                      . $html;
            }

            if ($parent === $container) {
                // at the root of the given container
                break;
            }

            $active = $parent;
        }

        return strlen($html) ? $this->getIndent() . $html : '';
    }

    /**
     * Renders the given $container by invoking the partial view helper
     *
     * The container will simply be passed on as a model to the view script,
     * so in the script it will be available in <code>$this->container</code>.
     *
     * @param  Container $container [optional] container to pass to view script.
     *                              Default is to use the container registered 
     *                              in the helper.
     * @param  string|array $partial [optional] partial view script to use. 
     *                               Default is to use the partial registered 
     *                               in the helper.  If an array is given, it 
     *                               is expected to contain two values; the 
     *                               partial view script to use, and the module 
     *                               where the script can be found.
     * @return string               helper output
     * @throws Exception\RuntimeException if no partial provided
     * @throws Exception\InvalidArgumentException if partial is invalid array
     */
    public function renderPartial(Container $container = null,
                                  $partial = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        if (null === $partial) {
            $partial = $this->getPartial();
        }

        if (empty($partial)) {
            throw new Exception\RuntimeException(
                'Unable to render menu: No partial view script provided'
            );
        }

        // put breadcrumb pages in model
        $model  = array('pages' => array());
        $active = $this->findActive($container);
        if ($active) {
            $active = $active['page'];
            $model['pages'][] = $active;
            while ($parent = $active->getParent()) {
                if ($parent instanceof AbstractPage) {
                    $model['pages'][] = $parent;
                } else {
                    break;
                }

                if ($parent === $container) {
                    // break if at the root of the given container
                    break;
                }

                $active = $parent;
            }
            $model['pages'] = array_reverse($model['pages']);
        }

        if (is_array($partial)) {
            if (count($partial) != 2) {
                throw new Exception\InvalidArgumentException(
                    'Unable to render menu: A view partial supplied as '
                    .  'an array must contain two values: partial view '
                    .  'script and module where script can be found'
                );
            }

            $partialHelper = $this->view->plugin('partial');
            return $partialHelper($partial[0], /*$partial[1], */$model);
        }

        $partialHelper = $this->view->plugin('partial');
        return $partialHelper($partial, $model);
    }

    // Zend\View\Helper\Navigation\Helper:

    /**
     * Renders helper
     *
     * Implements {@link Helper::render()}.
     *
     * @param  Container $container [optional] container to render. Default is
     *                              to render the container registered in the helper.
     * @return string               helper output
     */
    public function render(Container $container = null)
    {
        $partial = $this->getPartial();
        if ($partial) {
            return $this->renderPartial($container, $partial);
        } else {
            return $this->renderStraight($container);
        }
    }
}
