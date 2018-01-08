<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-11-07
 * Time: 10:04
 */

namespace Inhere\Validate;

use Inhere\Validate\Utils\Helper;

/**
 * Class FieldValidation
 * - one field to many rules. like Laravel framework
 * ```php
 * $vd = FieldValidation::make($data, $rules, ...);
 * $vd->validate();
 * ```
 * @package Inhere\Validate
 */
class FieldValidation extends AbstractValidation
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            // ['field', 'required|string:5,10|...', ...],
            // ['field0', ['required', 'string:5,10'], ...],
            // ['field1', 'rule1|rule2|...', ...],
            // ['field2', 'rule1|rule3|...', ...],
        ];
    }

    /**
     * @return \Generator
     * @throws \InvalidArgumentException
     */
    protected function collectRules()
    {
        $scene = $this->scene;

        // 循环规则, 搜集当前场景可用的规则
        foreach ($this->getRules() as $rule) {
            // check field
            if (!isset($rule[0]) || !$rule[0]) {
                throw new \InvalidArgumentException('Please setting the field(string) to wait validate! position: rule[0].');
            }

            // check validators
            if (!isset($rule[1]) || !$rule[1]) {
                throw new \InvalidArgumentException('The field validators must be is a validator name(s) string! position: rule[1].');
            }

            // an rule for special scene.
            if (!empty($rule['on'])) {
                if (!$scene) {
                    continue;
                }

                $sceneList = \is_string($rule['on']) ? Helper::explode($rule['on']) : (array)$rule['on'];

                if (!\in_array($scene, $sceneList, true)) {
                    continue;
                }

                unset($rule['on']);
            }

            $this->_usedRules[] = $rule;
            $field = array_shift($rule);

            // if is a Closure
            if (\is_object($rule[0])) {
                yield $field => $rule;
            } else {
                // 'required|string:5,10;' OR 'required|in:5,10'
                $rules = \is_array($rule[0]) ? $rule[0] : array_map('trim', explode('|', $rule[0]));

                foreach ($rules as $aRule) {
                    $rule = $this->parseRule($aRule, $rule);

                    yield $field => $rule;
                }
            }
        }
    }

    /**
     * @param string $rule
     * @param array $row
     * @return array
     */
    protected function parseRule($rule, $row)
    {
        $rule = trim($rule, ': ');

        if (false === strpos($rule, ':')) {
            $row[0] = $rule;
            return $row;
        }

        list($name, $args) = explode(':', $rule, 2);
        $args = trim($args, ', ');
        $row[0] = $name;

        switch ($name) {
            case 'in':
            case 'enum':
            case 'ontIn':
                $row[] = array_map('trim', explode(',', $args));
                break;

            case 'size':
            case 'range':
            case 'string':
            case 'between':
                if (strpos($args, ',')) {
                    list($row['min'], $row['max']) = array_map('trim', explode(',', $args, 2));
                } else {
                    $row['min'] = $args;
                }
                break;
            default:
                $args = strpos($args, ',') ? array_map('trim', explode(',', $args)) : [$args];
                $row = array_merge($row, $args);
                break;
        }

        return $row;
    }
}
