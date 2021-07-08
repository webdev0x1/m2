<?php
namespace Ocacia\Swatches\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Ocacia\Swatches\Block\Adminhtml\Form\Field\CustomColumn;

class DynamicFieldData extends AbstractFieldArray
{
    /**
     * @var CustomColumn
     */
    private $dropdownRenderer;

    protected $_template = 'Magento_Config::system/config/form/field/array.phtml';

    protected function _construct()
    {
        if (!$this->_addButtonLabel) {
            $this->_addButtonLabel = __('Add');
	}
	$this->setTemplate('Ocacia_Swatches::system/config/form/field/array.phtml');
        parent::_construct();
    }

    /**
     * Prepare existing row data object
     * 
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'attribute_name',
            [
                'label' => __('Attribute name'),
                'class' => 'required-entry',
            ]
        );

        $this->addColumn(
            'attribute_value',
            [
                'label' => __('Attribute value'),
                'class' => 'required-entry',
            ]
        );
        $this->addColumn(
		'swatch_image',
            [
		    'label' => __('Swatch Image'),
		    'class' => 'required-entry',
                'renderer' => $this->getDropdownRenderer(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        // override in descendants
    }

    /**
     * @return CustomColumn
     * @throws LocalizedException
     */
    private function getDropdownRenderer()
    {
        if (!$this->dropdownRenderer)
	{

	    //$column = $this->_columns['swatch_image'];
            //$inputName = $this->_getCellInputElementName('swatch_image');
            $this->dropdownRenderer = $this->getLayout()->createBlock(
                \Ocacia\Swatches\Block\Adminhtml\SwatchRenderer::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]);//->setInputName($inputName)->setInputId($this->_getCellInputElementId('<%- _id %>', $columnName))->setColumnName('swatch_image')->setColumn($column);
        }
        return $this->dropdownRenderer;
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new \Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->_getCellInputElementName($columnName);

        if ($column['renderer']) {
            return $column['renderer']->setInputName(
                $inputName
            )->setInputId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setColumnName(
                $columnName
            )->setColumn(
                $column
            )->toHtml();
        }

        return '<input type="text" id="' . $this->_getCellInputElementId(
            '<%- _id %>',
            $columnName
        ) .
            '"' .
            ' name="' .
            $inputName .
            '" value="<%- ' .
            $columnName .
            ' %>" ' .
            ($column['size'] ? 'size="' .
            $column['size'] .
            '"' : '') .
            ' class="' .
            (isset($column['class'])
                ? $column['class']
                : 'input-text') . '"' . (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '/>';
    }

}
