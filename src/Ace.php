<?php

declare(strict_types=1);

namespace EnjoysCMS\ContentEditor\AceEditor;

use Enjoys\AssetsCollector\Assets;
use EnjoysCMS\Core\Components\ContentEditor\ContentEditorInterface;
use Enjoys\AssetsCollector;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class Ace implements ContentEditorInterface
{

    private ?string $selector = null;

    /**
     * @throws \Exception
     */
    public function __construct(
        private Environment $twig,
        private AssetsCollector\Assets $assets,
        private LoggerInterface $logger,
        private ?string $template = null
    ) {
        if (!file_exists(__DIR__ . '/../node_modules/ace-builds')) {
            throw new \RuntimeException(sprintf('Run: cd %s && yarn install', realpath(__DIR__.'/..')));
        }

        $this->initialize();
    }

    private function getTemplate(): ?string
    {
        return $this->template ?? __DIR__.'/template/html.twig';
    }

    /**
     * @throws \Exception
     */
    private function initialize()
    {
        $path = str_replace(getenv('ROOT_PATH'), '', realpath(__DIR__ . '/../'));

        AssetsCollector\Helpers::createSymlink(
            sprintf('%s/assets%s/node_modules/ace-builds', $_ENV['PUBLIC_DIR'], $path),
            __DIR__ . '/../node_modules/ace-builds',
            $this->logger
        );

        $this->assets->add(
            'js',
            [
                [
                    __DIR__ . '/../node_modules/ace-builds/src-noconflict/ace.js',
                    AssetsCollector\AssetOption::ATTRIBUTES => [
                        "type" => "text/javascript",
                        "charset" => "utf-8"
                    ]
                ],
                __DIR__ . '/../node_modules/ace-builds/src-noconflict/ext-language_tools.js',
            ]
        );
    }


    public function setSelector(string $selector): void
    {
        $this->selector = $selector;
    }

    public function getSelector(): string
    {
        if ($this->selector === null) {
            throw new \RuntimeException('Selector not set');
        }
        return $this->selector;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function getEmbedCode(): string
    {
        $twigTemplate = $this->getTemplate();
        if (!$this->twig->getLoader()->exists($twigTemplate)) {
            throw new \RuntimeException(
                sprintf("ContentEditor: (%s): Нет шаблона в по указанному пути: %s", self::class, $twigTemplate)
            );
        }
        return $this->twig->render(
            $twigTemplate,
            [
                'editor' => $this,
                'selector' => $this->getSelector()
            ]
        );
    }
}
