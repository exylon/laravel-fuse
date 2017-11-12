<?php


namespace Exylon\Fuse\Repositories\Database\Concerns;


trait HasOptions
{

    /**
     * @var array
     */
    protected $defaultOptions = [
        'column_updated_at' => 'updated_at',
        'column_created_at' => 'created_at'
    ];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Use options
     *
     * @param array $options
     *
     * @return $this
     */
    public function withOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Overrides the default settings for this repository
     *
     * @param array $defaultOptions
     */
    public function setDefaultOptions(array $defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;
    }

    protected function getOptions()
    {
        return array_merge(
            config('fuse.repository') ?: [],
            $this->defaultOptions ?: [],
            $this->options ?: []);
    }

    protected function resetOptions()
    {
        $this->options = [];
    }
}
