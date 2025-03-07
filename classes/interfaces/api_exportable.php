<?php namespace webservice_api\interfaces;

interface api_exportable {

    /**
     * Exports as it can be presented in an API response
     *
     * @return object
     */
    public function to_api_representation() : object;
}
