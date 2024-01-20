<?php
namespace Littled\Request;

use mysqli;
use Littled\Validation\Validation;

class IntegerInput extends RenderedInput
{
    /** @var string         Form input element template filename */
    protected static string $input_template_filename = 'string-text-input.php';
    /** @var string         Input container filename template */
    protected static string $template_filename = 'string-text-field.php';
    /** @var string         Data type identifier used with bind_param() calls */
    protected static string $bind_param_type = 'i';
    const DEFAULT_DATA_SIZE = 8;

    public function __construct(string $label, string $key, bool $required = false, $value = null, int $size_limit = 0, ?int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
        $this->setInputValue($value);
        $this->content_type = 'number';
    }

    /**
	 * Collects the value corresponding to the $param property value in GET, POST, session, or cookies.
	 * @param ?array $src Collection of input data. If not specified, will read input from POST, GET, Session vars.
	 * @param ?string $key Key to use in place of the internal $key property value.
	 */
	public function collectRequestData(?array $src=null, ?string $key=null)
	{
		if ($this->bypass_collect_request_data===true) {
			return;
		}
		$this->value = Validation::collectIntegerRequestVar((($key)?:($this->key)), null, $src);
	}

	/**
	 * @inheritDoc
	 */
	public function collectAjaxRequestData(object $data)
	{
		parent::collectAjaxRequestData($data);
		$this->value = Validation::parseInteger($this->value);
	}

	/**
	 * @inheritDoc
	 */
	public function escapeSQL(mysqli $mysqli, bool $include_quotes=false): ?int
	{
		return Validation::parseInteger($this->value);
	}

    /**
     * {@inheritDoc}
     */
    public function safeValue($options=[]): string
    {
        if (!is_numeric($this->value) && !is_array($this->value)) {
            return ('');
        }
        return parent::safeValue($options);
    }

    /**
	 * @param null|int|int[] $value Value to assign as the value of the object.
	 */
	public function setInputValue($value)
	{
        if (is_array($value)) {
            $this->value = [];
            foreach($value as $el) {
                $el = Validation::parseInteger($el);
                if (is_int($el)) {
                    $this->value[] = $el;
                }
            }
        }
        else {
            $this->value = Validation::parseInteger($value);
        }
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate()
	{
		if ((false === $this->isEmpty()) && (null === Validation::parseInteger($this->value))) {
			$this->throwValidationError(ucfirst($this->label)." is in unrecognized format.");
		}
		parent::validate();
	}
}