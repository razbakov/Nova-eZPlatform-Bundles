<?php

/**
 * NovaeZSlackBundle Bundle.
 *
 * @package   Novactive\Bundle\eZSlackBundle
 *
 * @author    Novactive <s.morel@novactive.com>
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaeZSlackBundle/blob/master/LICENSE MIT Licence
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZSlackBundle\Core\Slack\Interaction\Provider\Action;

use eZ\Publish\API\Repository\Events;
use Novactive\Bundle\eZSlackBundle\Core\Slack\Action;
use Novactive\Bundle\eZSlackBundle\Core\Slack\Attachment;
use Novactive\Bundle\eZSlackBundle\Core\Slack\Button;
use Novactive\Bundle\eZSlackBundle\Core\Slack\InteractiveMessage;
use Symfony\Contracts\EventDispatcher\Event;

class Publish extends ActionProvider
{
    public function getAction(Event $event, int $index): ?Action
    {
        $content = $this->getContentForSignal($event);
        if (
            null === $content || $content->contentInfo->published ||
            $event instanceof Events\Trash\TrashEvent ||
            $event instanceof Events\Trash\RecoverEvent
        ) {
            return null;
        }
        $button = new Button($this->getAlias(), '_t:action.publish', (string) $content->id);
        $button->setStyle(Button::PRIMARY_STYLE);

        return $button;
    }

    public function getNewAction(Event $event): ?array
    {
        $content = $this->getContentForSignal($event);
        if (
            null === $content || $content->contentInfo->published ||
            $event instanceof Events\Trash\TrashEvent ||
            $event instanceof Events\Trash\RecoverEvent
        ) {
            return null;
        }

        return [
            'text' => $this->translator->trans('action.publish', [], 'slack'),
            'action_id' => $this->getAlias(),
            'value' => (string) $content->id,
            'style' => ActionProvider::PRIMARY_STYLE
        ];
    }

//    public function execute(InteractiveMessage $message): Attachment
//    {
//        $action = $message->getAction();
//        $value = (int) $action->getValue();
//
//        $attachment = new Attachment();
//        $attachment->setTitle('_t:action.publish');
//        try {
//            $content = $this->repository->getContentService()->loadContent($value);
//            $this->repository->getContentService()->publishVersion($content->versionInfo);
//            $attachment->setColor('good');
//            $attachment->setText('_t:action.content.published');
//        } catch (\Exception $e) {
//            $attachment->setColor('danger');
//            $attachment->setText($e->getMessage());
//        }
//
//        return $attachment;
//    }

    public function execute(InteractiveMessage $message, array $actions = []): array
    {
        $action = $message->getAction();
        $value = (int) $action['value'];

        return [];
    }
}
