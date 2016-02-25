<?php
namespace J2PC;

class Generator
{
    private $classes = [];

    public function generateFromJson($json, $className, $namespace = null)
    {
        $o = json_decode($json);
        $this->processClass($o, $className);
        return $this->generate($namespace);
    }

    private function processClass($o, $className, $ref = null)
    {
        $ref = $ref ?? uniqid();
        $this->classes[$ref] = [
            'name' => $className,
            'structure' => $this->buildRepresentation($o),
        ];
    }

    private function buildRepresentation($o)
    {
        $properties = [];
        foreach (get_object_vars($o) as $k => $var) {
            $type = $this->guessType($var);
            $properties[$k] = ['type' => $type];
            if ($type === 'class') {
                $ref = uniqid();
                $properties[$k]['ref'] = $ref;
                $className = $this->toCamelCase($k);
                $this->processClass($var, $className, $ref);
            } elseif ($type === 'array') {
                $typesArray = $this->guessTypeArray($var);
                if (count($typesArray) === 1) {
                    $typeArray = $typesArray[0];
                    if ($typeArray === 'class') {
                        // TODO Check if same class by signature
                        list($oneClass, $classes) = $this->arrayComposedBySameClass($var);
                        if ($oneClass) {
                            $ref = uniqid();
                            $className = $this->toCamelCase(rtrim($k, 's'));
                            $properties[$k] = ['type' => $className.'[]'];
                            $this->processClass($var[0], $className, $ref);
                        } else {
                            foreach ($classes as $i => $class) {
                                $ref = uniqid();
                                $className = $this->toCamelCase(rtrim($k, 's').$i);
                                $this->processClass($class, $className, $ref);
                            }
                            $properties[$k] = ['type' => 'mixed[]'];
                        }
                    } elseif ($typeArray === 'array') {
                        // TODO nested array, need to extract the array representation
                        $properties[$k] = ['type' => $typeArray.'[]'];
                    } else {
                        $properties[$k] = ['type' => $typeArray.'[]'];
                    }
                } else {
                    if (in_array('class', $typesArray)) {
                        // TODO generate mixed classes
                    }
                    $properties[$k] = ['type' => 'mixed[]'];
                }
            }
        }
        return $properties;
    }

    private function arrayComposedBySameClass($array)
    {
        $classesCount = 0;
        $classesStructures = [];
        $classesBodies = [];
        foreach ($array as $element) {
            $structure = array_keys(get_object_vars($element));
            if (!in_array($structure, $classesStructures)) {
                $classesStructures[] = $structure;
                $classesBodies[] = $element;
                $classesCount++;
            }
        }
        return [$classesCount === 1, $classesBodies];
    }

    private function guessTypeArray($array)
    {
        $types = [];
        foreach ($array as $k => $element) {
            $types[] = $this->guessType($element);
        }
        return array_unique($types);
    }

    private function guessType($var)
    {
        if (is_object($var)) {
            return 'class';
        } elseif (is_integer($var)) {
            return 'integer';
        } elseif (is_string($var)) {
            return 'string';
        } elseif (is_array($var)) {
            return 'array';
        } elseif (is_bool($var)) {
            return 'boolean';
        } elseif (is_double($var)) {
            return 'double';
        } elseif (is_float($var)) {
            return 'float';
        } elseif (is_long($var)) {
            return 'long';
        } elseif (is_scalar($var)) {
            return 'scalar';
        } else {
            throw new \Exception('Unknown type for '. print_r($var, true), 1);
        }
    }

    private function generate($namespace)
    {
        $classesGenerated = [];
        foreach ($this->classes as $class) {
            $classesGenerated[$class['name']] = $this->generateClass($class, $namespace);
        }
        return $classesGenerated;
    }

    private function generateClass($class, $namespace)
    {
        $name = $class['name'];
        $strNamespace = $namespace ? "namespace ".$namespace.";\n" : "";
        $body = <<<HEAD
<?php
$strNamespace
class $name
{
HEAD;
        foreach ($class['structure'] as $name=>$var) {
            $body .= $this->generateVar($name, $var, $namespace);
        }

        $body .= <<<FOOTER
}
FOOTER;

        return $body;
    }

    private function generateVar($name, $var, $namespace)
    {
        $type = $var['type'] === 'class' ? $this->classes[$var['ref']]['name'] : $var['type'];
        $strNamespace = $var['type'] === 'class' && $namespace ? $namespace.'\\' : '';
        $body = <<<VAR

    /**
     * @var $strNamespace$type
     */
     protected \$$name;

VAR;
        return $body;
    }

    private function toCamelCase(string $str)
    {
        $str = ucfirst($str);
        return preg_replace_callback('/_([a-z])/', function($match) {
            return strtoupper($match[1]);
        }, $str);
    }
}