<?php
class LeafletField extends FormField {

    protected $data;

	/**
     * @config
     */
    private static $map_options = array();

	/**
     * @config
     */
    private static $draw_options = array();

    /**
	 * @var FormField
	 */
	protected $geometryField;

    /**
	 * @var array
	 */
    protected $options = array();

	/**
	 * @param string $name The name of the field
	 * @param string $title The title of the field
	 */
	public function __construct($name, $title = null, DataObject $data) {
		$this->data = $data;

		// setup the option defaults
		$this->options = array(
			'map' => $this->config()->map_options,
			'draw' => $this->config()->draw_options
		);

		$this->setupChildren($name);

		parent::__construct($name, $title, $data->$name);
	}

	/**
	 * Set up child hidden fields.
	 * @return FieldList
	 */
	public function setupChildren($name) {
		$this->geometryField = HiddenField::create(
			$name . '[Geometry]',
			'Geometry',
			$this->data->$name
		)->addExtraClass('leafletfield-geometry');

		$this->children = new FieldList(
			$this->geometryField
		);

		return $this->children;
	}

	public function Field($properties = array()) {
		// set the html js attributes
		$this->setAttribute('data-map-options', $this->getMapOptionsJS());
		$this->setAttribute('data-draw-options', $this->getDrawOptionsJS());
		
		// set the dependencies
		$this->requireDependencies();
		
		return parent::Field($properties);
	}

	protected function requireDependencies() {
		Requirements::javascript('//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js');
		Requirements::javascript('//cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.2.3/leaflet.draw.js');
		Requirements::javascript(LEAFLETFIELD_BASE .'/javascript/LeafletField.js');
		Requirements::css('//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css');
		Requirements::css('//cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.2.3/leaflet.draw.css');
		Requirements::css(LEAFLETFIELD_BASE .'/css/LeafletField.css');
	}

	/**
	 * {@inheritdoc}
	 */
	public function setValue($value, $data = null) {

		if(is_array($value) && isset($value['Geometry'])) {
			$this->geometryField->setValue($value['Geometry']);
		} elseif(is_string($value)) {
			$this->geometryField->setValue($value);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveInto(DataObjectInterface $record) {
		if($this->name) {
			$record->setCastedField($this->name, $this->geometryField->dataValue());
		}
	}

	/**
	 * @return FieldList
	 */
	public function getChildFields() {
		return $this->children;
	}

	/**
	 * @return string
	 */
	public function getGeometry() {
		$fieldName = $this->getName();
		return $this->data->$fieldName;
	}

	/**
	 * Return the L.map options.
	 * @return Array
	 */
	public function getMapOptions() {
		return $this->options['map'];
	}

	/**
	 * Return the map options as a json string.
	 * @return String
	 */
	public function getMapOptionsJS() {
		return Convert::array2json($this->getMapOptions());
	}

	/**
	 * Set the map options, will override the config defaults.
	 * @param array $options
	 */
	public function setMapOptions($options = array()) {
		$this->options['map'] = array_merge($this->options['map'], $options);
	}

	/**
	 * Return the L.Control.Draw options.
	 * @return Array
	 */
	public function getDrawOptions() {
		return $this->options['draw'];
	}

	/**
	 * Return the draw options as a json string.
	 * @return String
	 */
	public function getDrawOptionsJS() {
		return Convert::array2json($this->getDrawOptions());
	}

	/**
	 * Set the draw options, will override the config defaults.
	 * @param array $options
	 */
	public function setDrawOptions($options = array()) {
		$this->options['draw'] = array_merge($this->options['draw'], $options);
	}

	public function setLimit(Int $limit) {
		$this->setMapOptions(array('layerLimit' => $limit));
	}

}
