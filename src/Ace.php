<?php

declare(strict_types=1);

namespace EnjoysCMS\ContentEditor\AceEditor;

use Enjoys\AssetsCollector;
use Enjoys\AssetsCollector\Assets;
use EnjoysCMS\Core\Components\ContentEditor\ContentEditorInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TwigFunction;


class Ace implements ContentEditorInterface
{

    private ?string $selector = null;
    private array $options = [
        'showLineNumbers' => 'true',
        'showPrintMargin' => 'true',
        'fontSize' => '14',
        'enableBasicAutocompletion' => 'true',
        'enableLiveAutocompletion' => 'false',
        'theme' => null,
        'mode' => 'html'
    ];

    /**
     * @throws \Exception
     */
    public function __construct(
        private Environment $twig,
        private AssetsCollector\Assets $assets,
        private LoggerInterface $logger,
        private ?string $template = null,
        array $options = []
    ) {
        if (!file_exists(__DIR__ . '/../node_modules/ace-builds')) {
            throw new \RuntimeException(sprintf('Run: cd %s && yarn install', realpath(__DIR__ . '/..')));
        }

        $this->options = array_merge($this->options, $options);

        $this->initialize();
    }

    private function getTemplate(): ?string
    {
        return $this->template ?? __DIR__ . '/template/html.twig';
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

        /**
         * TODO: spl_object_id - in doubt
         */
        $this->twig->registerUndefinedFunctionCallback(
            function ($name) {
                if ($name === 'spl_object_id'){
                    return new TwigFunction('spl_object_id', function ($data){
                        return spl_object_id($data);
                    });
                }
                return false;
            }
        );

        if (!$this->twig->getLoader()->exists($twigTemplate)) {
            throw new \RuntimeException(
                sprintf("ContentEditor: (%s): Нет шаблона в по указанному пути: %s", self::class, $twigTemplate)
            );
        }
        return $this->twig->render(
            $twigTemplate,
            [
                'editor' => $this,
                'selector' => $this->getSelector(),
                'options' => $this->options
            ]
        );
    }
}
