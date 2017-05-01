<?php
namespace Psmb\GoogleFormsCaptcha\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;

class ProxyController extends ActionController
{
    /**
     * @Flow\InjectConfiguration(path="forms")
     * @var array
     */
    protected $forms;

    /**
     * @Flow\InjectConfiguration(path="secret")
     * @var string
     */
    protected $secret;

    /**
     * Check captcha and then relay form input to Google Forms and display its response
     *
     * @param string $preset
     * @return string
     * @see https://developers.google.com/recaptcha/docs/verify
     */
    public function submitAction($preset) {
        if (!$this->forms[$preset]) {
            throw new \Exception('Invalid preset supplied');
        }
        if (!$_POST['g-recaptcha-response']) {
            throw new Exception('Captcha response not supplied');
        }

        $formKey = $this->forms[$preset];

        $browser = new Browser();
        $browser->setRequestEngine(new CurlEngine());

        $arguments = ['secret' => $this->secret, 'response' => $_POST['g-recaptcha-response']];
        $captchaResponse = $browser->request('https://www.google.com/recaptcha/api/siteverify', 'POST', $arguments);
        $responseArray = json_decode($captchaResponse->getContent(), true);
        if (!$responseArray['success']) {
            throw new Exception('Validation did not pass, try again');
        }

        // Proxy to Google Forms
        $formsResponse = $browser->request('https://docs.google.com/forms/d/' . $formKey . '/formResponse?embedded=true', 'POST', $_POST);
        return $formsResponse->getContent();
    }
}
