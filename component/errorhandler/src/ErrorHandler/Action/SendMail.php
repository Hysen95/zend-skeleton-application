<?php

namespace ErrorHandler\Action;

use ErrorHandler\Model\Error;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class SendMail extends AbstractAction implements ActionInterface
{
	
	protected $toEmails;
	protected $fromEmail;
	protected $fromName;

	protected $useSmtpTransport = false;
	protected $transportConfig;

	public function setConfig($config)
	{
		foreach (array('fromEmail', 'toEmails', 'SMTP') as $configKey)
		{
			if (!isset($config[$configKey]))
				throw new \Exception('ErrorHandler: $config does not contain '.$configKey.' value.');
		}

		$this->fromEmail = $config['fromEmail'];
		$this->fromName = $config['fromName'];
		$this->toEmails = $config['toEmails'];
		if (is_string($this->toEmails))
			$this->toEmails = array($this->toEmails);

		foreach (array('enabled', 'config') as $configKey)
		{
			if (!isset($config['SMTP'][$configKey]))
				throw new \Exception('ErrorHandler: $config does not contain SMTP['.$configKey.'] value.');
		}

		$this->useSmtpTransport = $config['SMTP']['enabled'];
		$this->transportConfig = $config['SMTP']['config'];

		parent::setConfig($config);
	}

    public function run(Error $error)
    {
    	$subject = '[error] '.$error->getError().' ('.$error->getStatusCode().')';

        $text = new MimePart($error->getLogAsString());
		$text->type = "text/plain";

		$body = new MimeMessage();
		$body->setParts(array($text));

		$message = new Message();
		$message->setEncoding('UTF-8');
	    $message->setSubject($subject);
		$message->setBody($body);

		return $this->sendMessage($message);
    }

    public function sendMessage(Message $message)
    {
		$message->addFrom($this->fromEmail, $this->fromName);

		foreach ($this->toEmails as $email)
	    	$message->addTo($email);

		/* Send email */
		$sent = false;

		try {
			if ($this->useSmtpTransport)
			{
				$transport = new SmtpTransport();
				$options   = new SmtpOptions($this->transportConfig);
				$transport->setOptions($options);
				$transport->send($message);
			}
			else
			{
				$transport = new SendmailTransport();
				$transport->send($message);
			}
			$sent = true;
		}
		catch (\Exception $e)
		{
			$sent = false;
		}

		return $sent;
    }
} 
