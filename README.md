# ace-editor
Ace (Ajax.org Cloud9 Editor) fo EnjoysCMS

### Variants (example) config
```yaml
\EnjoysCMS\ContentEditor\AceEditor\Ace:
    template: # (string|null) default `null`
    options:
      showLineNumbers: 'true' # (string<bool>)
      showPrintMargin: 'true' # (string<bool>)
      fontSize: 14 # (number)
      enableBasicAutocompletion: 'true' # (string<bool>)
      enableLiveAutocompletion: 'false' # (string<bool>)
      theme: null # (string|null)
      mode: html # (string)
```

```yaml
editor: \EnjoysCMS\ContentEditor\AceEditor\Ace
```

```yaml
editor: 
  \EnjoysCMS\ContentEditor\AceEditor\Ace: 'template.path'
```


```yaml
editor: 
  \EnjoysCMS\ContentEditor\AceEditor\Ace:
      template: 'template.path'
      options: # ... []
```
