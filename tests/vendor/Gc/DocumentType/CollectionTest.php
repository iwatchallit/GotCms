<?php
namespace Gc\DocumentType;

use Gc\Layout\Model as LayoutModel,
    Gc\User\Model as UserModel,
    Gc\View\Model as ViewModel,
    Zend\Db\Sql\Insert;
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-10-17 at 20:40:09.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection
     */
    protected $_object;

    /**
     * @var ViewModel
     */
    protected $_view;

    /**
     * @var LayoutModel
     */
    protected $_layout;

    /**
     * @var UserModel
     */
    protected $_user;

    /**
     * @var DocumentTypeModel
     */
    protected $_documentType;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_view = new ViewModel();
        $this->_view->setData(array(
            'name' => 'View Name',
            'identifier' => 'View identifier',
            'description' => 'View Description',
            'content' => 'View Content'
        ));
        $this->_view->save();

        $this->_layout = new LayoutModel();
        $this->_layout->setData(array(
            'name' => 'Layout Name',
            'identifier' => 'Layout identifier',
            'description' => 'Layout Description',
            'content' => 'Layout Content'
        ));
        $this->_layout->save();

        $this->_user = new UserModel();
        $this->_user->setData(array(
            'lastname' => 'User test',
            'firstname' => 'User test',
            'email' => 'test@test.com',
            'login' => 'test',
            'user_acl_role_id' => 1,
        ));

        $this->_user->setPassword('test');
        $this->_user->save();

        $this->_documentType = new Model();
        $this->_documentType->setData(array(
            'name' => 'Document Type Name',
            'description' => 'Document Type description',
            'icon_id' => 1,
            'default_view_id' => $this->_view->getId(),
            'user_id' => $this->_user->getId(),
        ));

        $this->_documentType->save();

        $this->_object = new Collection();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->_object);

        $this->_documentTypeChildren->delete();
        unset($this->_documentTypeChildren);

        $this->_view->delete();
        unset($this->_view);

        $this->_layout->delete();
        unset($this->_layout);

        $this->_user->delete();
        unset($this->_user);
    }

    /**
     * @covers Gc\DocumentType\Collection::init
     * @covers Gc\DocumentType\Collection::setDocumentTypes
     */
    public function testInit()
    {
        $this->_object->init($this->_documentType->getId());
        $this->assertTrue(is_array($this->_object->getDocumentTypes()));
    }

    /**
     * @covers Gc\DocumentType\Collection::getSelect
     */
    public function testGetSelect()
    {
        $this->_object->init();
        $this->assertTrue(is_array($this->_object->getSelect()));
    }
}
