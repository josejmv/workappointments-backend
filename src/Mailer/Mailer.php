<?php

declare(strict_types=1);

namespace App\Mailer;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * class Mailer
 * @package App\Mailer\Mailer
 */
class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $emailSettings;

   /** 
    * @var WebsiteDomain
    */
   protected $websiteDomain;

   public function __construct(
        \Swift_Mailer $mailer,
        Environment $twig,
        array $emailSettings
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->emailSettings = $emailSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function sendRestorePasswordEmail($setting, $email, $confirm): int
    {
        $settings = $this->emailSettings[$setting];
        $from = $settings['from'];
        $context = array(
            'confirm' => $confirm
        );
        try{            
            return $this->sendMessage($settings, $context, $from, $email);
        }catch(Exception $error){
            return $error->getMessage();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendSuccessRestorePasswordEmail($setting, $email, $password): int
    {
        $settings = $this->emailSettings[$setting];
        $from = $settings['from'];
        $context = array(
            'password' => $password,
            'email' => $email
        );
        try{            
            return $this->sendMessage($settings, $context, $from, $email);
        }catch(Exception $error){
            return $error->getMessage();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendServiceEmail($setting, $email, $confirm, $name, $title, $description): int
    {
        $settings = $this->emailSettings[$setting];
        $from = $settings['from'];
        $context = array(
            'title' => $title,
            'description' => $description,
            'name' => $name,
            'confirm' => $confirm
        );
        try{            
            return $this->sendMessage($settings, $context, $from, $email);
        }catch(Exception $error){
            return $error->getMessage();
        }         
    }

    /**
     * {@inheritdoc}
     */
    public function sendAppointmentEmail($name, $email, $password, $setting, $confirm, $title, $description): int
    {
        $settings = $this->emailSettings[$setting];
        $from = $settings['from'];
        $context = array(
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'title' => $title,
            'description' => $description,
            'confirm' => $confirm
        );
        try{            
            return $this->sendMessage($settings, $context, $from, $email);
        }catch(Exception $error){
            return $error->getMessage();
        }         
    }

    /**
     * @param array $settings
     * @param array $context
     * @param array $fromEmail
     * @param string $toEmail
     *
     * @return int
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \Throwable
     */
    protected function sendMessage(
        array $settings,
        $context,
        $fromEmail,
        $toEmail
    ): int {
        $txtTemplate = $settings['template']['text'];
        $htmlTemplate = $settings['template']['html'];
        $subjectTemplate = $settings['template']['subject'];

        $templateSubject = $this->twig->load($subjectTemplate);

        $message = (new \Swift_Message());
        
        $context['logo'] = $message->embed(\Swift_Image::fromPath(dirname(__FILE__, 3) . '/templates/img/perfexya.png'));

        $subject = $templateSubject->renderBlock('subject', $context);
        $textBody = $this->twig->render($txtTemplate, $context);
        $htmlBody = $this->twig->render($htmlTemplate, $context);

        $message->setSubject($subject)
                ->setFrom($fromEmail)
                ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        return $this->mailer->send($message);
        // return true;
    }
}