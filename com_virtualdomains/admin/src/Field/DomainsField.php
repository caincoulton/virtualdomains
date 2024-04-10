<?php

namespace Janguo\Component\VirtualDomains\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Janguo\Component\VirtualDomains\Administrator\Model\VirtualDomainsModel;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

class DomainsField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Domains';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  4.0.0
     */
	protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   3.7.0
     */
    protected function getInput()
    {
        $data = $this->getLayoutData();

        $data['options'] = (array) $this->getOptions();

        $domainsModel = new VirtualDomainsModel([
            'ignore_request' => true
        ]);
        $domainsModel->setState('list.ordering', 'domain');
        $domains = $domainsModel->getItems();

        foreach ($domains as $domain)
        {
            $options[] = HTMLHelper::_('select.option', $domain->id, $domain->domain);
        }

        $data['options'] = array_merge((array) $this->getOptions(), $options);

        return $this->getRenderer($this->layout)->render($data);
    }
}