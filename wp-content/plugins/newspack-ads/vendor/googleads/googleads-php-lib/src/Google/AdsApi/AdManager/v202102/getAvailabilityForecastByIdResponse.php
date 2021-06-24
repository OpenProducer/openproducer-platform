<?php

namespace Google\AdsApi\AdManager\v202102;


/**
 * This file was generated from WSDL. DO NOT EDIT.
 */
class getAvailabilityForecastByIdResponse
{

    /**
     * @var \Google\AdsApi\AdManager\v202102\AvailabilityForecast $rval
     */
    protected $rval = null;

    /**
     * @param \Google\AdsApi\AdManager\v202102\AvailabilityForecast $rval
     */
    public function __construct($rval = null)
    {
      $this->rval = $rval;
    }

    /**
     * @return \Google\AdsApi\AdManager\v202102\AvailabilityForecast
     */
    public function getRval()
    {
      return $this->rval;
    }

    /**
     * @param \Google\AdsApi\AdManager\v202102\AvailabilityForecast $rval
     * @return \Google\AdsApi\AdManager\v202102\getAvailabilityForecastByIdResponse
     */
    public function setRval($rval)
    {
      $this->rval = $rval;
      return $this;
    }

}
