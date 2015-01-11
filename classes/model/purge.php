<?php

namespace Foolz\Foolfuuka\Plugins\URIMediaPurge\Model;

use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\Model;
use Foolz\Foolframe\Model\Preferences;

use Foolz\Foolfuuka\Model\Comment;
use Foolz\Foolfuuka\Model\CommentBulk;
use Foolz\Foolfuuka\Model\CommentFactory;
use Foolz\Foolfuuka\Model\Media;
use Foolz\Foolfuuka\Model\MediaFactory;

class URIMediaPurge extends Model
{
    /**
     * @var DoctrineConnection
     */
    protected $dc;

    /**
     * @var Preferences
     */
    protected $preferences;

    /**
     * @var RadixCollection
     */
    protected $radix_coll;

    /**
     * @var MediaFactory
     */
    protected $media_factory;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->dc = $context->getService('doctrine');
        $this->preferences = $context->getService('preferences');
        $this->radix_coll = $context->getService('foolfuuka.radix_collection');
        $this->media_factory = $this->getContext()->getService('foolfuuka.media_factory');
    }

    public function findBoard($shortname = null)
    {
        if ($this->radix_coll->getByShortname($shortname) === false) {
            return null;
        }

        $boards = $this->radix_coll->getAll();
        foreach ($boards as $board) {
            if (!is_null($shortname) && $shortname == $board->shortname) {
                return $board;
            }
        }
    }

    public function findMedia($shortname = null, $filename = null, $thumb = false)
    {
        if ($filename == null || ($board = $this->findBoard($shortname)) == null) {
            return null;
        }

        if ($thumb === true) {
            return $this->dc->qb()
                ->select('*')
                ->from($board->getTable('_images'), 'bi')
                ->where('preview_op = :thumb')
                ->orWhere('preview_reply = :thumb')
                ->setParameter(':thumb', $filename)
                ->execute()
                ->fetch();
        } else {
            return $this->dc->qb()
                ->select('*')
                ->from($board->getTable('_images'), 'bi')
                ->where('media = :media')
                ->setParameter(':media', $filename)
                ->execute()
                ->fetch();
        }
    }

    public function process($input)
    {
        $uris = preg_split('/\r\n|\r|\n/', $input);
        $data = [];

        foreach ($urls as $link) {
            if (preg_match('/\/(\w+)\/(thumb|image)\/(?:\d+)\/(?:\d+)\/((?:\d+)s?\.(?:\w+))$/', $link, $match)) {
                try {
                    $radix = $this->findBoard($match[1]);
                    $image = $this->findMedia($match[1], $match[3], (($match[2] == 'thumb') ? true : false));

                    if ($radix !== null || $image !== null) {
                        $media = $this->media_factory->getByMediaId($radix, $image['media_id']);
                        $media = new Media($this->getContext(), CommentBulk::forge($radix, null, $media));
                        $media->ban(true);
                    }
                } catch (\Foolz\Foolfuuka\Model\MediaNotFoundException $e) {

                }
            }
        }

        return $data;
    }
}
