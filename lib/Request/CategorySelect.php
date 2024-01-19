<?php
namespace Littled\Request;

use Exception;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Keyword\Keyword;
use Littled\Log\Log;
use Littled\PageContent\ContentUtils;
use Littled\Utility\LittledUtility;
use Littled\Validation\ValidationErrors;


class CategorySelect extends MySQLConnection
{
    protected static int    $content_type_id;
    protected static string $container_template = 'category-select-container.php';

    /** @var Keyword[] */
    public array            $categories=[];
    public StringSelect     $category_input;
    public StringTextField  $new_category;
    protected int           $parent_id;
    public ValidationErrors $validation_errors;

    public function __construct()
    {
        parent::__construct();
        $this->category_input = new StringSelect('Category', 'catTerm', false, [], 100);
        $this->category_input->setAllowMultiple();
        $this->new_category = new StringTextField('New category', 'catNew', false, '', 100);
        $this->validation_errors = new ValidationErrors();
    }

    /**
     * Flag indicating that multiple category values can be collected.
     * @param bool $allow Optional flag indicating if multiple values are allowed. Defaults to TRUE.
     * @return void
     */
    public function setAllowMultiple(bool $allow=true)
    {
        $this->category_input->setAllowMultiple($allow);
    }

    /**
     * Collect and store client request data.
     * @return void
     * @throws ConfigurationUndefinedException
     */
    public function collectRequestData()
    {
        foreach($this as $property) {
            if (is_object($property) && method_exists($property, 'collectRequestData')) {
                $property->collectRequestData();
            }
        }
        if ($this->category_input->allow_multiple) {
            foreach($this->category_input->value as $term) {
                $this->pushKeywordInstance($term);
            }
        }
        else {
            if ($this->category_input->value) {
                $this->pushKeywordInstance($this->category_input->value);
            }
        }
        if ($this->new_category->value &&
            !in_array($this->new_category->value, $this->getCategoryTermList())) {
            $this->pushKeywordInstance($this->new_category->value);
        }
    }

    /**
     * Deletes associated keyword records from the database.
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    public function deleteRecords()
    {
        foreach($this->categories as $term) {
            $term->delete();
        }
    }

    /**
     * Returns current list of all category options. Just returns the internal values as they are. Doesn't perform
     * any database retrieval, like e.g. ::retrieveCategoryOptions()
     * @return array
     */
    public function getCategoryOptionList(): array
    {
        return $this->category_input->getOptions();
    }

    /**
     * Returns all the current category terms as a string array.
     * @return string[]
     */
    public function getCategoryTermList(): array
    {
        return array_map(function ($e) { return $e->term->value; }, $this->categories);
    }

    /**
     * Container template filename getter.
     * @return string
     */
    public static function getContainerTemplateFilename(): string
    {
        return static::$container_template;
    }

    /**
     * Container template full path getter.
     * @return string
     */
    public static function getContainerTemplatePath(): string
    {
        return LittledUtility::joinPaths(RequestInput::getTemplateBasePath(), static::getContainerTemplateFilename());
    }

    /**
     * Content type id value getter.
     * @return int
     * @throws ConfigurationUndefinedException
     */
    public static function getContentTypeId(): int
    {
        if (!isset(static::$content_type_id)) {
            throw new ConfigurationUndefinedException(Log::getShortMethodName().' Category content type not configured.');
        }
        return static::$content_type_id;
    }

    /**
     * Parent record id value getter.
     * @return ?int
     */
    public function getParentId(): ?int
    {
        return $this->parent_id ?? null;
    }

	/**
	 * Returns flag indicating that the object is currently has some keyword terms attached to it.
	 * @return bool
	 */
	public function hasKeywordData(): bool
	{
		return (count($this->categories) > 0);
	}

    /**
     * Returns boolean value indicating that this object has existing validation errors to report.
     * @return bool
     */
    public function hasValidationErrors(): bool
    {
        return $this->validation_errors->hasErrors();
    }

    /**
     * Tests if the instance is in possession of a valid parent record id.
     * @return bool
     */
    public function hasValidParent(): bool
    {
        return (isset($this->parent_id) && $this->parent_id > 0);
    }

    /**
     * Push a term as a Keyword object instance onto the internal stack of keywords.
     * @param string $term
     * @return void
     * @throws ConfigurationUndefinedException
     */
    protected function pushKeywordInstance(string $term)
    {
        $this->categories[] = new Keyword($term, $this->getParentId(), static::getContentTypeId());
    }

    /**
     * For a single parent record, retrieve from the database all categories linked to that parent record.
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    public function read()
    {
        if (!$this->hasValidParent() || !isset(static::$content_type_id)) {
            throw new ConfigurationUndefinedException(Log::getShortMethodName().' Parent properties not configured. ');
        }
        $query = 'SELECT `id`, `term` FROM `keyword` WHERE parent_id = ? AND type_id = ?';
        $type_id = static::getContentTypeId();
        $data = $this->fetchRecords($query, 'ii',$this->parent_id, $type_id);
        foreach($data as $row) {
            $this->categories[] = new Keyword($row->term, $this->parent_id, static::getContentTypeId());
        }
        $this->category_input->value = $this->getCategoryTermList();
    }

    /**
     * Injects class property values into template and prints result.
     * @return void
     */
    public function render()
    {
        ContentUtils::renderTemplateWithErrors(
            static::getContainerTemplatePath(),
            array('category_inputs' => &$this)
        );
    }

    /**
     * Returns list of strings with all category terms in use for this content type.
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    public function retrieveCategoryOptions()
    {
        $query = 'SELECT term FROM keyword WHERE type_id = ? GROUP BY term';
        $content_type_id = static::getContentTypeId();
        $conn = new MySQLConnection();
        $data = $conn->fetchRecords($query, 'i', $content_type_id);
        $options = array_map(function ($e) { return $e->term; }, $data);
        $options = array_combine($options, $options);
        $this->category_input->setOptions($options);
    }

    /**
     * Commit category terms to database.
     * @throws Exception
     */
    public function save()
    {
        foreach($this->categories as $category) {
            $category->save();
        }
    }

    /**
     * @param string $class_name
     * @return $this
     */
    public function setContainerCSSClass(string $class_name): CategorySelect
    {
        $this->category_input->setContainerCSSClass($class_name);
        $this->new_category->setContainerCSSClass($class_name);
        return $this;
    }

    /**
     * Container template filename setter.
     * @param string $filename
     * @return void
     */
    public static function setContainerTemplateFilename(string $filename)
    {
        static::$container_template = $filename;
    }

    /**
     * @param string $class_name
     * @return $this
     */
    public function setListInputCSSClass(string $class_name): CategorySelect
    {
        $this->category_input->setInputCSSClass($class_name);
        return $this;
    }

    /**
     * Parent record id setter.
     * @param int $parent_id
     * @return void
     */
    public function setParentId( int $parent_id )
    {
        $this->parent_id = $parent_id;
		foreach($this->categories as $term) {
			$term->parent_id->value = $parent_id;
		}
    }

    /**
     * Specify if data is required for this input.
     * @param bool $required
     * @return void
     */
    public function setRequired(bool $required=true)
    {
        if ($required) {
            $this->category_input->setAsRequired();
        }
        else {
            $this->category_input->setAsNotRequired();
        }
    }

    /**
     * @param string $class_name
     * @return $this
     */
    public function setTextInputCSSClass(string $class_name): CategorySelect
    {
        $this->new_category->setInputCSSClass($class_name);
        return $this;
    }

    /**
     * Validates category form data.
     * @return void
     * @throws ContentValidationException
     */
    public function validateInput()
    {
        $this->validation_errors->clear();
        $cat_error = $new_cat_error = false;
        try {
            $this->category_input->validate();
        }
        catch(ContentValidationException $e) {
            $this->validation_errors->push($e->getMessage());
            $cat_error = true;
        }

        // if a category value is expected, it can be provided either by selecting from pre-existing categories
        // ($category_input property) or it can come from the new category field ($new_category property)
        $original = $this->new_category->required;
        try {
			if ($this->category_input->required && $this->category_input->has_errors) {
				$this->new_category->required = $this->category_input->required;
			}
            $this->new_category->validate();
        }
        catch(ContentValidationException $e) {
            $this->validation_errors->push($e->getMessage());
            $new_cat_error = true;
        }
        $this->new_category->required = $original;

        if ($cat_error && $new_cat_error) {
            throw new ContentValidationException($this->category_input->formatErrorLabel()." is required.");
        }
    }

    /**
     * Gets current error messages as an array.
     * @return array
     */
    public function validationErrors(): array
    {
        return $this->validation_errors->getList();
    }
}