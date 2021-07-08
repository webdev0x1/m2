<?php

namespace Ocacia\Swatches\Block\Adminhtml\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;

class ArraySerialized extends ConfigValue
{
    protected $serializer;

    public function __construct(
        SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
	TypeListInterface $cacheTypeList,
	\Ocacia\Swatches\Model\ImageUploader $imageUploader,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
	$this->imageUploader = $imageUploader;
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
	    $value = $this->getValue();
        	unset($value['__empty']);
	    $encodedValue = $this->serializer->serialize($value);
	    reset($value);
	    $oldValue = $this->getOldValue();
	    
	     $oldValue = $this->serializer->unserialize($oldValue);
	    foreach($value as $key => $val) {
		    if(!empty($val['swatch_image']['tmp_name']) && !empty($val['swatch_image']['name'])) {
//			    print_r($val['swatch_image']);
		    	$result = $this->imageUploader->saveFileToTmpDir($val['swatch_image']); //$value[key($value)]['swatch_image']
			$this->imageUploader->moveFileFromTmp($result['name']);
			$value[$key]['swatch_image']['name'] = $result['name'];
		    } else {
			    	    if(isset($oldValue[$key]) && isset($oldValue[$key]['swatch_image']))
					    $value[$key]['swatch_image'] = $oldValue[$key]['swatch_image'];

		    }
	    }
	    $encodedValue = $this->serializer->serialize($value);
            $this->setValue($encodedValue);
    }

    protected function _afterLoad()
    {
	    $value = $this->getValue();
        if ($value) {
		$decodedValue = $this->serializer->unserialize($value);
//		$decodedValue[key($decodedValue)]['swatch_image']['src'] = ''; 
            $this->setValue($decodedValue);
        }
    }
}
