<?php

namespace Grav\Plugin\Newsletter;

use Grav\Plugin\Form\Form;

class ValuesHydrator
{
    public function getValues(Form $form, array $fields = []): array
    {
        $values = [];
        $formValues = $form->getValues()->toArray()['data'];
        foreach ($fields as $field) {
            $values[$field] = key_exists($field, $formValues) ? $formValues[$field] : '';
        }
        return $values;
    }
}