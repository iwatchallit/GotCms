<?php
namespace Gc\Session\SaveHandler;

use Zend\Db\TableGateway\TableGateway,
    Zend\Session\SaveHandler\DbTableGatewayOptions;
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-10-17 at 19:48:38.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class DbTableGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DbTableGateway
     */
    protected $_object;

    /**
     * @var array
     */
    protected $_testArray;

    /**
     * @var TableGateway
     */
    protected $_adapter;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $tablegateway_config =  new DbTableGatewayOptions(array(
            'idColumn'   => 'id',
            'nameColumn' => 'name',
            'modifiedColumn' => 'updated_at',
            'lifetimeColumn' => 'lifetime',
            'dataColumn' => 'data',
        ));

        $this->_adapter = new TableGateway('core_session', \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter());
        $this->_object = new DbTableGateway($this->_adapter, $tablegateway_config);

        $this->_testArray = array('foo' => 'bar', 'bar' => array('foo' => 'bar'));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->_object);
    }

    /**
     * @covers Gc\Session\SaveHandler\DbTableGateway::read
     * @covers Gc\Session\SaveHandler\DbTableGateway::write
     */
    public function testRead()
    {
        $this->_object->open('savepath', 'sessionname');

        $id = '242';

        $this->assertTrue($this->_object->write($id, serialize($this->_testArray)));

        $data = unserialize($this->_object->read($id));
        $this->assertEquals($this->_testArray, $data, 'Expected ' . var_export($this->_testArray, 1) . "\nbut got: " . var_export($data, 1));
    }
    /**
     * @covers Gc\Session\SaveHandler\DbTableGateway::read
     * @covers Gc\Session\SaveHandler\DbTableGateway::write
     */
    public function testReadWithLifetimeExpired()
    {
        $this->_object->open('savepath', 'sessionname');

        $id = '242';

        $this->assertTrue($this->_object->write($id, serialize($this->_testArray)));

        $this->_adapter->update(array('lifetime' => 0), array('id' => $id));

        $data = $this->_object->read($id);
        $this->assertEquals('', $data);
    }

    /**
     * @covers Gc\Session\SaveHandler\DbTableGateway::write
     * @todo   Implement testWrite().
     */
    public function testWrite()
    {
        $this->_object->open('savepath', 'sessionname');
        $id = '242';
        $this->assertTrue($this->_object->write($id, serialize($this->_testArray)));
    }
}
