<?php

namespace Ocacia\Swatches\Model\Config\Backend;

class Image extends \Magento\Config\Model\Config\Backend\File
{
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'svg'];
    }
}
