<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2019-01-20
 * Time: 00:34
 */

namespace Inhere\Validate\Traits;

use Generator;
use Inhere\Validate\Filter\Filters;
use InvalidArgumentException;
use function array_map;
use function array_merge;
use function array_shift;
use function explode;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function strpos;
use function trim;

/**
 * Trait MultipleRulesTrait - allow add multiple rules like Laravel.
 * @package Inhere\Validate\Traits
 */
trait MultipleRulesTrait
{
    /**
     * Add rules like Laravel
     * @return array
     */
    /*public function rules()
    {
        return [
            ['field', 'required|string:5,10|...', ...],
            ['field0', ['required', 'string:5,10'], ...],
            ['field1', 'rule1|rule2|...', ...],
            ['field2', 'rule1|rule3|...', ...],
        ];
    }
    */

    /**
     * @return Generator
     * @throws InvalidArgumentException
     */
    protected function collectRules(): ?Generator
    {
        $scene = $this->scene;

        // 循环规则, 搜集当前场景可用的规则
        foreach ($this->getRules() as $rule) {
            // check field
            if (!isset($rule[0]) || !$rule[0]) {
                throw new InvalidArgumentException('Please setting the field(string) to wait validate! position: rule[0]');
            }

            // check validators
            if (!isset($rule[1]) || !$rule[1]) {
                throw new InvalidArgumentException('The field validators must be is a validator name(s) string! position: rule[1]');
            }

            // an rule for special scene.
            if (!empty($rule['on'])) {
                if (!$scene) {
                    continue;
                }

                $sceneList = is_string($rule['on']) ? Filters::explode($rule['on']) : (array)$rule['on'];

                if (!in_array($scene, $sceneList, true)) {
                    continue;
                }

                unset($rule['on']);
            }

            $this->_usedRules[] = $rule;
            // field
            $field = array_shift($rule);

            // if is a Closure
            if (is_object($rule[0])) {
                yield $field => $rule;
            } else {
                // 'required|string:5,10;' OR 'required|in:5,10'
                $rules = is_array($rule[0]) ? $rule[0] : array_map('trim', explode('|', $rule[0]));

                foreach ($rules as $aRule) {
                    yield $field => $this->parseRule($aRule, $rule);
                }
            }
        }
    }

    /**
     * @param string $rule
     * @param array  $row
     *
     * @return array
     */
    protected function parseRule(string $rule, array $row): array
    {
        $rule = trim($rule, ': ');

        if (false === strpos($rule, ':')) {
            $row[0] = $rule;
            return $row;
        }

        [$name, $args] = Filters::explode($rule, ':', 2);
        $args   = trim($args, ', ');
        $row[0] = $name;

        switch ($name) {
            case 'in':
            case 'enum':
            case 'ontIn':
                $row[] = Filters::explode($args);
                break;

            case 'size':
            case 'range':
            case 'string':
            case 'between':
                if (strpos($args, ',')) {
                    [$row['min'], $row['max']] = Filters::explode($args, ',', 2);
                } else {
                    $row['min'] = $args;
                }
                break;
            default:
                $row = array_merge($row, Filters::explode($args));
                break;
        }

        return $row;
    }
}
