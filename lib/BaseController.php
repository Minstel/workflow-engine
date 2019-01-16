<?php

/**
 * Base class for controllers.
 */
abstract class BaseController extends Jasny\Controller
{
    use Jasny\Controller\RouteAction;

    /**
     * Output data as json
     *
     * @param mixed $result
     * @param string $format  Mime or content format
     */
    protected function output($result, $format = 'json')
    {
        return parent::output($result, $format);
    }

    /**
     * Check if the given date is the last modified date.
     *
     * @param int|string|DateTime|null  $input  Timestamp, date string or DateTime
     * @return bool
     */
    protected function isModifiedSince($input): bool
    {
        if (App::env('dev') || App::env('tests')) {
            // caching may hinder developing and tests
            return true;
        }

        if (is_null($input)) {
            return true; // resource may have been deleted so we can't cache it
        }

        $ifModifiedSince = $this->getRequest()->getHeaderLine('If-Modified-Since') ?: -1;

        if (is_int($input)) {
            $date = $input;
        } elseif (is_string($input)) {
            $date = (new DateTime($input))->getTimestamp();
        } elseif ($input instanceof DateTimeInterface) {
            $date = $input->getTimestamp();
        } else {
            $date = 0; // oldest timestamp: January 1st, 1970
        }

        $lastModified = gmdate('D, d M Y H:i:s', $date) . ' GMT';
        header('Cache-Control: must-revalidate');
        header('Last-Modified: ' . $lastModified);

        return strtotime($lastModified) != strtotime($ifModifiedSince);
    }
    
    /**
     * Set the Not Modified (304) header
     */
    protected function notModified()
    {
        header("HTTP/1.1 304 Not Modified");
    }
}
