<?php

declare(strict_types=1);

namespace Modestox\AdminStickyNotes\Service\Admin\Ui;

use Modestox\AdminStickyNotes\Service\Admin\Ui\Component\Field;
use Modestox\ConfigProcessorWp\Admin\Ui\Common\Field\Text;

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
                $field->isRequired ? ' <span class="required" style="color:red;">*</span>' : ''
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
        // Формируем ту самую универсальную конфигурационную прослойку для AbstractField
        $fieldData = [
            '_forced_name'  => $field->id,         // Переопределяем имя инпута: name="title", name="message"
            '_forced_value' => $value,             // Подставляем значение из нашей сущности в обход get_option()
            'placeholder'   => $field->label,
            'comment'       => '',
        ];

        // Маршрутизируем типы на твои ООП-классы полей
        switch ($field->type) {
            case 'text':
                $fieldUi = new Text();
                $fieldUi->render($field->id, $fieldData);
                break;

            case 'textarea':
                // В будущем, когда создашь класс Textarea в пакете ConfigProcessorWp,
                // ты просто заменишь этот фолбэк на: $fieldUi = new Textarea();
                echo sprintf(
                    '<textarea name="%s" id="crud_%s" class="large-text" rows="5" required="required">%s</textarea>',
                    esc_attr($field->id),
                    esc_attr($field->id),
                    esc_textarea((string)$value)
                );
                break;

            default:
                // Фолбэк для базового текстового поля
                $fieldUi = new Text();
                $fieldUi->render($field->id, $fieldData);
                break;
        }
    }
}