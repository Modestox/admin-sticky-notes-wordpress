<?php

/**
 * Modestox CMS - E-commerce Platform
 *
 * @copyright Copyright (c) 2026 Sergey Kuzmitsky
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/Modestox/modestox
 */

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Shared\Ui;

use Modestox\AdminStickyNotes\Shared\Ui\Component\Field;
use Modestox\ConfigProcessorWp\Admin\Ui\Common\Field\Text;
use Modestox\ConfigProcessorWp\Admin\Ui\Common\Field\Textarea;
use Modestox\ConfigProcessorWp\Admin\Ui\Common\Field\Select;
use Modestox\ConfigProcessorWp\Admin\Ui\Common\Field\MultiSelect;
use Modestox\ConfigProcessorWp\Admin\Ui\Common\Field\Number;
use Modestox\ConfigProcessorWp\Admin\Ui\Common\Field\DateTime;

/**
 * Universal layout factory engine bridging abstract definitions with external decoupled UI Kit fields.
 */
final readonly class FormRenderer
{
    /**
     * Renders the complete HTML form layout structure by delegating inputs to shared UI classes.
     *
     * @param array<int, Field> $fields
     * @param array<string, mixed> $values Current model dataset values for editing context.
     * @return void
     */
    public function render(array $fields, array $values = []): void
    {
        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';

        foreach ($fields as $field) {
            $currentValue = $values[$field->id] ?? '';

            echo '<tr>';
            echo sprintf(
                '<th scope="row"><label for="crud_%s">%s%s</label></th>',
                esc_attr($field->id),
                esc_html($field->label),
                $field->isRequired ? ' <span class="required" style="color:red;">*</span>' : '',
            );
            echo '<td>';

            $this->renderExternalField($field, $currentValue);

            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    }

    /**
     * Binds internal fields definitions into extended external class instances.
     *
     * @param Field $field
     * @param mixed $value
     * @return void
     */
    private function renderExternalField(Field $field, mixed $value): void
    {
        $fieldData = [
            '_forced_name'  => $field->id,
            '_forced_value' => $value,
            'placeholder'   => $field->label,
            'comment'       => '',
            'required'      => $field->isRequired,
        ];

        switch ($field->type) {
            case 'text':
                $fieldUi = new Text();
                $fieldUi->render($field->id, $fieldData);
                break;

            case 'textarea':
                $fieldUi = new Textarea();
                $fieldUi->render($field->id, $fieldData);
                break;

            case 'number':
                $fieldUi = new Number();
                $fieldUi->render($field->id, $fieldData);
                break;

            case 'datetime':
                $fieldData['view_mode'] = 'datetime';
                $fieldUi = new DateTime();
                $fieldUi->render($field->id, $fieldData);
                break;

            case 'select':
                $formattedOptions = [];
                foreach ($field->options as $option) {
                    $formattedOptions[$option->value] = $option->label;
                }

                $fieldData['options'] = $formattedOptions;

                $fieldUi = new Select();
                $fieldUi->render($field->id, $fieldData);
                break;

            case 'multiselect':
                $formattedOptions = [];
                foreach ($field->options as $option) {
                    $formattedOptions[$option->value] = $option->label;
                }

                $fieldData['options'] = $formattedOptions;

                $fieldUi = new MultiSelect();
                $fieldUi->render($field->id, $fieldData);
                break;

            default:
                $fieldUi = new Text();
                $fieldUi->render($field->id, $fieldData);
                break;
        }
    }
}