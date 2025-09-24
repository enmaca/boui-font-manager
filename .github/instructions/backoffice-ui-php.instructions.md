# Instrucciones de Copilot para proyectos Laravel que usan Backoffice UI (PHP)

Estas directrices están pensadas para copiarse en `.github/instructions/backoffice-ui-php.instructions.md` dentro de los proyectos Laravel que integran el paquete `uxmaltech/backoffice-ui`. Su objetivo es ayudar a Copilot a generar código que respete la arquitectura fluida de Backoffice UI, aproveche los builders existentes y mantenga la coherencia con los servicios y componentes del paquete.【F:src/UxmalBackofficeUIServiceProvider.php†L31-L160】

## Principios generales del API fluido

1. **Utiliza los builders existentes antes de crear clases nuevas.** Casi todos los elementos de UI derivan de `Uxmal\Backoffice\Support\HtmlElement`, el cual provee manejo de atributos, dataset, clases CSS, visibilidad bootstrap y renderizado HTML a través de `toHtml()`/`render()`. Encadena métodos como `attribute()`, `id()`, `dataset()`, `append()` o `when()` en lugar de manipular strings manualmente.【F:src/Support/HtmlElement.php†L26-L596】
2. **Respeta la semántica de cada componente.** Los módulos de acciones, formularios, layouts y componentes UI exponen métodos declarativos (`->icon()`, `->tabs()`, `->placeholder()`, etc.) que deben usarse para configurar el comportamiento deseado en vez de tocar atributos arbitrarios.【F:src/UI/Card.php†L15-L300】【F:src/Form/Input/Select.php†L15-L232】
3. **Finaliza los builders llamando `toHtml()` o pasando la instancia al render pipeline.** Muchos componentes retornan HTML al convertirlos a string o implementan `Htmlable`; evita generar vistas duplicadas y reusa estos métodos de salida.【F:src/UI/Accordion.php†L10-L185】【F:src/UI/TableListJS.php†L10-L221】
4. **Encapsula la lógica JavaScript y recursos.** Si un componente necesita assets, regístralo mediante `UxmalBackofficeUIResourceManager` y respeta los helpers existentes para acciones JavaScript (`Dispatch`, `Fetch`, `Javascript`, `SubmitForm`).【F:src/UxmalBackofficeUIResourceManager.php†L11-L349】【F:src/Actions/Javascript.php†L7-L72】

## Integración con el ecosistema Laravel

- **Service Provider y registros automáticos.** `UxmalBackofficeUIServiceProvider` se encarga de ligar los servicios singleton (colecciones, resource manager, registradores de rutas). Cualquier nueva extensión debe añadirse en los métodos `register()` o `boot()` siguiendo los patrones existentes.【F:src/UxmalBackofficeUIServiceProvider.php†L31-L160】
- **Facades y helpers.** Para exponer funcionalidad en aplicaciones cliente, reutiliza las facades (`MasterLayoutFacade`, `SimpleLayoutFacade`, `PresignedS3Url`) o los helpers (`BackendRequest`, `NamedRoute`, `SideMenuItems`) en lugar de acceder directamente al contenedor.【F:src/Facades/MasterLayoutFacade.php†L14-L20】【F:src/Helpers/SideMenuItems.php†L12-L86】
- **Middlewares y layouts.** Los middlewares (`MasterLayoutMiddleware`, `RegisterDestinationsMiddleware`, `CheckEnabledServicesMiddleware`) envuelven la experiencia de backoffice. Mantén su uso cuando definas rutas nuevas y extiende los layouts (`BaseLayout`, `MasterLayout`, `ChatbotLayout`) con los métodos disponibles para insertar contenido y recursos.【F:src/Middleware/MasterLayoutMiddleware.php†L12-L50】【F:src/Layouts/MasterLayout.php†L15-L557】
- **Acceso a recursos y colecciones.** Usa `FormsCollection`, `ModalCollection`, `OffcanvasCollection` y demás servicios singleton para compartir componentes entre vistas o acciones sin duplicar estado.【F:src/Services/FormsCollection.php†L8-L56】【F:src/Services/ModalCollection.php†L5-L46】

## Convenciones al escribir prompts o plantillas

- Prefiere describir el resultado deseado (por ejemplo: “crear un modal con botón de cierre y disparador de Livewire”) y deja que Copilot utilice los métodos fluentes disponibles (`Modal::body()`, `Modal::trigger()`, etc.).【F:src/UI/Modal.php†L11-L328】
- Cuando requieras integrar JavaScript o assets, menciona explícitamente el uso de `resourceManager()->addJSResource()`/`addCSSResource()` o el registro automático del componente para garantizar que Copilot añada las dependencias correctas.【F:src/UxmalBackofficeUIResourceManager.php†L11-L349】
- Para flujos Livewire o acciones asíncronas, indica la utilización de los helpers `Dispatch`, `Fetch`, `SubmitForm` o `Javascript` según corresponda, aprovechando sus métodos (`->event()`, `->endpoint()`, `->target()`, etc.).【F:src/Actions/Dispatch.php†L7-L54】【F:src/Actions/SubmitForm.php†L11-L73】

## Guía de clases y métodos fluent por módulo


### Acciones
- `Uxmal\Backoffice\Actions\Action` Métodos fluentes: ->setCollection(), ->validateElement(), ->getUxID(). 【F:src/Actions/Action.php†L9-L62】
- `Uxmal\Backoffice\Actions\Dispatch` Métodos fluentes: ->event(), ->getAction(), ->getAttributes(). 【F:src/Actions/Dispatch.php†L7-L54】
- `Uxmal\Backoffice\Actions\Fetch` Métodos fluentes: ->confirm(), ->fetch(), ->endpoint(), ->getAttributes(), ->getAction(). 【F:src/Actions/Fetch.php†L8-L60】
- `Uxmal\Backoffice\Actions\Javascript` Métodos fluentes: ->call(), ->code(), ->getAction(), ->getAttributes(). 【F:src/Actions/Javascript.php†L7-L72】
- `Uxmal\Backoffice\Actions\SubmitForm` Métodos fluentes: ->target(), ->action(), ->getAction(), ->getAttributes(). 【F:src/Actions/SubmitForm.php†L11-L73】

### Advance UI
- `Uxmal\Backoffice\AdvanceUI\ProductCard` Métodos fluentes: ->title(), ->subtitle(), ->showDeleteButton(), ->toHtml(). 【F:src/AdvanceUI/ProductCard.php†L12-L80】
- `Uxmal\Backoffice\AdvanceUI\SortableContainer` Métodos fluentes: ->item(), ->listGroup(), ->disabled(), ->horizontal(), ->onDragEnd(), ->handle(). 【F:src/AdvanceUI/SortableContainer.php†L9-L122】
- `Uxmal\Backoffice\AdvanceUI\SwiperSlider` Métodos fluentes: ->swiperType(), ->slides(), ->getOptionsDefinitions(), ->swiperOptions(), ->direction(), ->withPagination(). 【F:src/AdvanceUI/SwiperSlider.php†L11-L320】
- `Uxmal\Backoffice\AdvanceUI\Wysiwyg` Métodos fluentes: ->placeholder(), ->theme(). 【F:src/AdvanceUI/Wysiwyg.php†L8-L61】

### Atributos y registros automáticos
- `Uxmal\Backoffice\Attributes\RegisterController` 【F:src/Attributes/RegisterController.php†L8-L16】

### Comandos Artisan
- `Uxmal\Backoffice\Console\Commands\InitApplicationTokenCommand` Métodos fluentes: ->handle(). 【F:src/Console/Commands/InitApplicationTokenCommand.php†L9-L62】
- `Uxmal\Backoffice\Console\Commands\InitMasterLayout` Métodos fluentes: ->handle(), ->replaceInFile(). 【F:src/Console/Commands/InitMasterLayout.php†L8-L54】
- `Uxmal\Backoffice\Console\Commands\InstallNodeDependencies` Métodos fluentes: ->handle(), ->buildNpmInstallString(). 【F:src/Console/Commands/InstallNodeDependencies.php†L8-L88】
- `Uxmal\Backoffice\Console\Commands\TestCommand` Métodos fluentes: ->handle(). 【F:src/Console/Commands/TestCommand.php†L9-L60】
- `Uxmal\Backoffice\Console\Commands\UxmalTechUICommand` Métodos fluentes: ->handle(). 【F:src/Console/Commands/UxmalTechUICommand.php†L12-L303】

### Componentes Livewire
- `Uxmal\Backoffice\Livewire\Autocomplete` Métodos fluentes: ->mount(), ->query(), ->updatedSearch(), ->render(). 【F:src/Livewire/Autocomplete.php†L11-L92】

### Componentes UI
- `Uxmal\Backoffice\UI\Accordion` Métodos fluentes: ->bsStyle(), ->collapsed(), ->accordionOptions(), ->addItem(), ->getHeadings(), ->getContents(). 【F:src/UI/Accordion.php†L10-L185】
- `Uxmal\Backoffice\UI\Alert` Métodos fluentes: ->getIconClass(), ->alertOptions(), ->icon(), ->heading(), ->bsStyle(), ->body(). 【F:src/UI/Alert.php†L14-L209】
- `Uxmal\Backoffice\UI\Badge` Métodos fluentes: ->bsStyle(), ->badgeOptions(). 【F:src/UI/Badge.php†L9-L116】
- `Uxmal\Backoffice\UI\Breadcrumb` Métodos fluentes: ->items(), ->olClass(). 【F:src/UI/Breadcrumb.php†L9-L61】
- `Uxmal\Backoffice\UI\Canvas` Métodos fluentes: ->content(), ->toHtml(), ->isStaticContent(). 【F:src/UI/Canvas.php†L10-L65】
- `Uxmal\Backoffice\UI\Canvas\CanvasElement` Métodos fluentes: ->initialPosition(), ->input(), ->output(), ->linkTo(), ->getUxName(), ->toHtml(). 【F:src/UI/Canvas/CanvasElement.php†L10-L86】
- `Uxmal\Backoffice\UI\Card` Métodos fluentes: ->toolbar(), ->header(), ->body(), ->footer(), ->bsStyle(), ->borderBSStyle(). 【F:src/UI/Card.php†L15-L300】
- `Uxmal\Backoffice\UI\Card\ToolbarButton` Métodos fluentes: ->collapse(), ->close(), ->custom(). 【F:src/UI/Card/ToolbarButton.php†L9-L74】
- `Uxmal\Backoffice\UI\Carousel` Métodos fluentes: ->slide(), ->item(), ->carouselOptions(), ->getControls(), ->getIndicators(), ->toHtml(). 【F:src/UI/Carousel.php†L9-L123】
- `Uxmal\Backoffice\UI\Carousel\Slide` Métodos fluentes: ->active(), ->toHtml(). 【F:src/UI/Carousel/Slide.php†L8-L50】
- `Uxmal\Backoffice\UI\Chatbot` Métodos fluentes: ->messages(), ->action(), ->options(), ->customActions(), ->placeholderContent(), ->initialMessage(). 【F:src/UI/Chatbot.php†L13-L272】
- `Uxmal\Backoffice\UI\CodeFlask` Métodos fluentes: ->language(), ->code(), ->toHtml(). 【F:src/UI/CodeFlask.php†L10-L72】
- `Uxmal\Backoffice\UI\Cropper` Métodos fluentes: ->image(), ->cropperOptions(), ->aspectRatio(), ->zoomable(), ->dragMode(), ->viewMode(). 【F:src/UI/Cropper.php†L16-L261】
- `Uxmal\Backoffice\UI\Dropdown` Métodos fluentes: ->buttonLabel(), ->split(), ->offset(), ->bsStyle(), ->size(), ->renderButton(). 【F:src/UI/Dropdown.php†L12-L200】
- `Uxmal\Backoffice\UI\Flatpickr` Métodos fluentes: ->enableTime(), ->dateFormat(), ->mode(), ->option(), ->options(), ->getOptions(). 【F:src/UI/Flatpickr.php†L10-L105】
- `Uxmal\Backoffice\UI\GridJS` Métodos fluentes: ->queryEndPoint(), ->setColumns(), ->searchable(), ->setRows(), ->setPagination(), ->setMultipleColumnSort(). 【F:src/UI/GridJS.php†L21-L453】
- `Uxmal\Backoffice\UI\GridJS\Column` Métodos fluentes: ->plain(), ->html(), ->action(), ->rowSelection(), ->id(), ->rowRender(). 【F:src/UI/GridJS/Column.php†L16-L268】
- `Uxmal\Backoffice\UI\GridJS\Filter` Métodos fluentes: ->radio(), ->checkbox(), ->date(). 【F:src/UI/GridJS/Filter.php†L5-L140】
- `Uxmal\Backoffice\UI\GridJS\Pagination` Métodos fluentes: ->create(), ->setDefaultLanguage(), ->toArray(), ->setLimit(), ->setPreviousText(), ->setNextText(). 【F:src/UI/GridJS/Pagination.php†L10-L147】
- `Uxmal\Backoffice\UI\Highlight` Métodos fluentes: ->language(), ->showLineNumbers(), ->code(), ->toHtml(). 【F:src/UI/Highlight.php†L15-L108】
- `Uxmal\Backoffice\UI\Icon` Métodos fluentes: ->ril(), ->ris(), ->ri(), ->bx(), ->bxs(), ->bxl(). 【F:src/UI/Icon.php†L9-L160】
- `Uxmal\Backoffice\UI\ListGroup` Métodos fluentes: ->item(), ->setFlush(), ->alignHorizontal(), ->toHtml(). 【F:src/UI/ListGroup.php†L9-L91】
- `Uxmal\Backoffice\UI\MarkdownEditor` Métodos fluentes: ->markdown(), ->previewStyle(), ->placeholder(), ->height(), ->disabled(), ->autofocus(). 【F:src/UI/MarkdownEditor.php†L8-L94】
- `Uxmal\Backoffice\UI\MenuDropdown` Métodos fluentes: ->buildLiMenuItem(), ->toHtml(). 【F:src/UI/MenuDropdown.php†L10-L257】
- `Uxmal\Backoffice\UI\Modal` 【F:src/UI/Modal.php†L11-L328】
- `Uxmal\Backoffice\UI\Offcanvas` Métodos fluentes: ->trigger(). 【F:src/UI/Offcanvas.php†L9-L183】
- `Uxmal\Backoffice\UI\SideMenu` Métodos fluentes: ->title(), ->item(). 【F:src/UI/SideMenu.php†L12-L52】
- `Uxmal\Backoffice\UI\SideMenu\Item` Métodos fluentes: ->icon(), ->route(), ->items(), ->target(), ->groupPath(), ->toHtml(). 【F:src/UI/SideMenu/Item.php†L12-L184】
- `Uxmal\Backoffice\UI\SideMenu\Title` 【F:src/UI/SideMenu/Title.php†L8-L21】
- `Uxmal\Backoffice\UI\Tab` Métodos fluentes: ->tabType(), ->tabOptions(), ->bsStyle(), ->tabs(). 【F:src/UI/Tab.php†L12-L150】
- `Uxmal\Backoffice\UI\Table` Métodos fluentes: ->toHtml(). 【F:src/UI/Table.php†L11-L43】
- `Uxmal\Backoffice\UI\Table\ModelTable` Métodos fluentes: ->previousPage(), ->nextPage(), ->setPage(), ->setOrder(), ->getNextOrderByData(), ->getOrderDirIcon(). 【F:src/UI/Table/ModelTable.php†L15-L343】
- `Uxmal\Backoffice\UI\Table\ModelTableColumn` Métodos fluentes: ->headerRender(), ->contentRender(), ->footerRender(), ->sortable(), ->searchable(), ->getSortDirection(). 【F:src/UI/Table/ModelTableColumn.php†L9-L82】
- `Uxmal\Backoffice\UI\Table\ModelTableRow` Métodos fluentes: ->render(). 【F:src/UI/Table/ModelTableRow.php†L10-L36】
- `Uxmal\Backoffice\UI\TableListJS` Métodos fluentes: ->ljsColumns(), ->ljsPagination(), ->ljsEnableSearch(), ->ljsWireTrack(), ->emptyLegend(), ->addToolBarButton(). 【F:src/UI/TableListJS.php†L10-L221】
- `Uxmal\Backoffice\UI\TemplateCanvas` Métodos fluentes: ->withHistory(), ->controls(), ->dimensions(), ->markers(), ->addObject(), ->addObjects(). 【F:src/UI/TemplateCanvas.php†L13-L186】
- `Uxmal\Backoffice\UI\Title` Métodos fluentes: ->size(), ->weight(), ->subtext(), ->toHtml(). 【F:src/UI/Title.php†L11-L85】
- `Uxmal\Backoffice\UI\Toggleable\Popover` Métodos fluentes: ->title(), ->placement(), ->trigger(), ->dismissable(), ->hoverable(), ->text(). 【F:src/UI/Toggleable/Popover.php†L10-L137】
- `Uxmal\Backoffice\UI\Toggleable\Tooltip` Métodos fluentes: ->placement(), ->html(), ->text(), ->trigger(), ->toHtml(), ->getAttributes(). 【F:src/UI/Toggleable/Tooltip.php†L9-L104】

### Componentes de fábrica
- `Uxmal\Backoffice\Components\Form` Métodos fluentes: ->wireSubmitPrevent(), ->action(), ->method(), ->enctype(), ->target(), ->acceptCharset(). 【F:src/Components/Form.php†L53-L379】
- `Uxmal\Backoffice\Components\Html` 【F:src/Components/Html.php†L57-L138】
- `Uxmal\Backoffice\Components\Input` Métodos fluentes: ->file(), ->text(), ->hidden(). 【F:src/Components/Input.php†L10-L35】
- `Uxmal\Backoffice\Components\Livewire` Métodos fluentes: ->autocomplete(). 【F:src/Components/Livewire.php†L7-L13】
- `Uxmal\Backoffice\Components\Pages` 【F:src/Components/Pages.php†L11-L28】
- `Uxmal\Backoffice\Components\UI` 【F:src/Components/UI.php†L74-L123】
- `Uxmal\Backoffice\Components\Widgets` 【F:src/Components/Widgets.php†L7-L24】

### Controladores
- `Uxmal\Backoffice\Controllers\UxmalRoutesController` 【F:src/Controllers/UxmalRoutesController.php†L10-L60】

### Elementos HTML bajos
- `Uxmal\Backoffice\Html\A` Métodos fluentes: ->href(), ->target(). 【F:src/Html/A.php†L7-L25】
- `Uxmal\Backoffice\Html\Button` Métodos fluentes: ->content(), ->btnType(), ->btnSize(), ->btnWidth(), ->btnStyle(), ->icon(). 【F:src/Html/Button.php†L18-L312】
- `Uxmal\Backoffice\Html\Div` 【F:src/Html/Div.php†L8-L19】
- `Uxmal\Backoffice\Html\DivCol` 【F:src/Html/DivCol.php†L18-L27】
- `Uxmal\Backoffice\Html\DivFlex` 【F:src/Html/DivFlex.php†L7-L16】
- `Uxmal\Backoffice\Html\DivRow` 【F:src/Html/DivRow.php†L7-L16】
- `Uxmal\Backoffice\Html\ModelTableElement` Métodos fluentes: ->query(), ->columns(), ->pageLength(), ->orderBy(), ->toHtml(). 【F:src/Html/ModelTableElement.php†L10-L80】
- `Uxmal\Backoffice\Html\Table` Métodos fluentes: ->columns(), ->caption(), ->rows(), ->row(), ->rowRender(), ->thHandler(). 【F:src/Html/Table.php†L19-L603】
- `Uxmal\Backoffice\Html\TableColumn` Métodos fluentes: ->tdClass(), ->thClass(), ->tdStyle(), ->thStyle(), ->headerRender(), ->contentRender(). 【F:src/Html/TableColumn.php†L10-L196】

### Elementos de formulario
- `Uxmal\Backoffice\Form\Checkbox` Métodos fluentes: ->bsStyle(), ->checkedValue(), ->value(), ->toHtml(). 【F:src/Form/Checkbox.php†L16-L87】
- `Uxmal\Backoffice\Form\ColorPicker` Métodos fluentes: ->label(), ->beforeInsertInput(). 【F:src/Form/ColorPicker.php†L21-L60】
- `Uxmal\Backoffice\Form\Date` Métodos fluentes: ->toHtml(). 【F:src/Form/Date.php†L15-L55】
- `Uxmal\Backoffice\Form\Dropzone` Métodos fluentes: ->fileNameHelper(), ->fileTypeHelper(), ->preSignedS3UploadUrl(), ->preSignedS3DeleteUrl(), ->listUrl(), ->uploadUrl(). 【F:src/Form/Dropzone.php†L15-L210】
- `Uxmal\Backoffice\Form\FormElement` Métodos fluentes: ->getInputElement(). 【F:src/Form/FormElement.php†L10-L22】
- `Uxmal\Backoffice\Form\Hidden` 【F:src/Form/Hidden.php†L10-L19】
- `Uxmal\Backoffice\Form\Input` 【F:src/Form/Input.php†L23-L325】
- `Uxmal\Backoffice\Form\Input\Checkbox` Métodos fluentes: ->checked(), ->card(), ->bsStyle(), ->beforeInsertInput(). 【F:src/Form/Input/Checkbox.php†L19-L93】
- `Uxmal\Backoffice\Form\Input\Color` 【F:src/Form/Input/Color.php†L8-L27】
- `Uxmal\Backoffice\Form\Input\File` Métodos fluentes: ->droppable(), ->maxFileSize(), ->filepond(). 【F:src/Form/Input/File.php†L13-L156】
- `Uxmal\Backoffice\Form\Input\Filepond\CallbackOptions` Métodos fluentes: ->setOnInit(), ->setOnWarning(), ->setOnError(), ->setOnProcessStart(), ->setOnProcessProgress(), ->setOnProcessComplete(). 【F:src/Form/Input/Filepond/CallbackOptions.php†L5-L102】
- `Uxmal\Backoffice\Form\Input\Filepond\Options` Métodos fluentes: ->setAllowDrop(), ->setAllowBrowse(), ->setAllowMultiple(), ->setAllowPaste(), ->setAllowReorder(), ->setAllowReplace(). 【F:src/Form/Input/Filepond/Options.php†L7-L246】
- `Uxmal\Backoffice\Form\Input\Filepond\ServerOptions` Métodos fluentes: ->setUrl(), ->setProcess(), ->setRevert(), ->setFetch(), ->setRestore(), ->setLoad(). 【F:src/Form/Input/Filepond/ServerOptions.php†L7-L64】
- `Uxmal\Backoffice\Form\Input\Filepond\ServerRequestOptions` Métodos fluentes: ->setUrl(), ->setMethod(), ->setWithCredentials(), ->setHeaders(), ->setTimeout(), ->setOnload(). 【F:src/Form/Input/Filepond/ServerRequestOptions.php†L7-L99】
- `Uxmal\Backoffice\Form\Input\Group` Métodos fluentes: ->prefix(), ->suffix(), ->toHtml(). 【F:src/Form/Input/Group.php†L14-L71】
- `Uxmal\Backoffice\Form\Input\Number` Métodos fluentes: ->step(), ->min(), ->max(). 【F:src/Form/Input/Number.php†L8-L51】
- `Uxmal\Backoffice\Form\Input\Password` Métodos fluentes: ->afterInsertInput(). 【F:src/Form/Input/Password.php†L18-L54】
- `Uxmal\Backoffice\Form\Input\Radio` Métodos fluentes: ->bsStyle(), ->checked(), ->beforeInsertInput(). 【F:src/Form/Input/Radio.php†L9-L60】
- `Uxmal\Backoffice\Form\Input\Range` Métodos fluentes: ->min(), ->max(), ->step(), ->value(), ->bsStyle(). 【F:src/Form/Input/Range.php†L9-L64】
- `Uxmal\Backoffice\Form\Input\SelChoices` Métodos fluentes: ->placeholder(), ->search(), ->cbqSearch(). 【F:src/Form/Input/SelChoices.php†L11-L192】
- `Uxmal\Backoffice\Form\Input\Select` Métodos fluentes: ->placeholder(), ->divClass(), ->options(), ->selected(), ->multiple(), ->createOptionsGroupElement(). 【F:src/Form/Input/Select.php†L15-L232】
- `Uxmal\Backoffice\Form\Input\SwitchToggle` Métodos fluentes: ->size(), ->alignRight(), ->toHtml(). 【F:src/Form/Input/SwitchToggle.php†L10-L49】
- `Uxmal\Backoffice\Form\Input\Text` Métodos fluentes: ->icon(), ->afterInsertInput(), ->toHtml(). 【F:src/Form/Input/Text.php†L18-L71】
- `Uxmal\Backoffice\Form\Input\TextArea` 【F:src/Form/Input/TextArea.php†L14-L31】
- `Uxmal\Backoffice\Form\Input\Toggle` Métodos fluentes: ->setOnLabel(), ->setOffLabel(), ->setOnStyle(), ->setOffStyle(), ->setOnValue(), ->setOffValue(). 【F:src/Form/Input/Toggle.php†L28-L165】
- `Uxmal\Backoffice\Form\Password` Métodos fluentes: ->toHtml(). 【F:src/Form/Password.php†L15-L88】
- `Uxmal\Backoffice\Form\Radio` Métodos fluentes: ->bsStyle(), ->label(), ->checked(), ->beforeInsertInput(). 【F:src/Form/Radio.php†L16-L76】
- `Uxmal\Backoffice\Form\Text` Métodos fluentes: ->icon(), ->inputAttribute(), ->inputStyle(), ->toHtml(). 【F:src/Form/Text.php†L17-L181】
- `Uxmal\Backoffice\Form\Time` Métodos fluentes: ->min(), ->max(), ->toHtml(). 【F:src/Form/Time.php†L15-L85】

### Eventos JavaScript
- `\JavaScriptEvents` Métodos fluentes: ->isValid(), ->allowedEvents(). 【F:src/JavaScriptEvents/GlobalEvents.php†L3-L375】

### Excepciones
- `Uxmal\Backoffice\Exceptions\BackofficeUiException` 【F:src/Exceptions/BackofficeUiException.php†L7-L10】

### Facades
- `Uxmal\Backoffice\Facades\MasterLayoutFacade` Métodos fluentes: ->getFacadeAccessor(). 【F:src/Facades/MasterLayoutFacade.php†L14-L20】
- `Uxmal\Backoffice\Facades\PresignedS3Url` Métodos fluentes: ->getFacadeAccessor(). 【F:src/Facades/PresignedS3Url.php†L19-L25】
- `Uxmal\Backoffice\Facades\SimpleLayoutFacade` Métodos fluentes: ->getFacadeAccessor(). 【F:src/Facades/SimpleLayoutFacade.php†L8-L14】

### Helpers
- `Uxmal\Backoffice\Helpers\BackendRequest` Métodos fluentes: ->command(), ->query(), ->getUrl(). 【F:src/Helpers/BackendRequest.php†L12-L116】
- `Uxmal\Backoffice\Helpers\DotNotationAccess` Métodos fluentes: ->get(). 【F:src/Helpers/DotNotationAccess.php†L7-L33】
- `Uxmal\Backoffice\Helpers\NamedRoute` Métodos fluentes: ->make(), ->setUseInComponent(), ->formatUri(), ->getAttributes(). 【F:src/Helpers/NamedRoute.php†L9-L115】
- `Uxmal\Backoffice\Helpers\PresignedS3Url` Métodos fluentes: ->disk(), ->prefix(), ->options(), ->expiresIn(), ->aclPublicRead(), ->contentType(). 【F:src/Helpers/PresignedS3Url.php†L7-L115】
- `Uxmal\Backoffice\Helpers\RegisterEndpointRoutes` Métodos fluentes: ->endpoint(), ->get(). 【F:src/Helpers/RegisterEndpointRoutes.php†L15-L62】
- `Uxmal\Backoffice\Helpers\RegisterSelfRoutes` Métodos fluentes: ->make(), ->path(), ->register(), ->registerWebRoutesFromAttributes(). 【F:src/Helpers/RegisterSelfRoutes.php†L21-L167】
- `Uxmal\Backoffice\Helpers\SideMenuItems` Métodos fluentes: ->addTitle(), ->addItem(), ->get(). 【F:src/Helpers/SideMenuItems.php†L12-L86】

### Layouts
- `Uxmal\Backoffice\Layouts\BaseLayout` Métodos fluentes: ->setTitle(), ->setHeadStyles(), ->viteAsset(), ->getViteAssets(), ->addBodyScripts(), ->setBodyScripts(). 【F:src/Layouts/BaseLayout.php†L9-L115】
- `Uxmal\Backoffice\Layouts\ChatbotLayout` Métodos fluentes: ->setAppIcon(), ->setLeftSidebar(), ->setTopBarToggleButton(), ->setTopBarUserInfo(), ->setTopBarContent(), ->setTopBarActionButtons(). 【F:src/Layouts/ChatbotLayout.php†L12-L285】
- `Uxmal\Backoffice\Layouts\ChatbotLayout\TopBarActionButton` Métodos fluentes: ->user(), ->themeToggle(), ->notifications(), ->settings(), ->minimize(), ->close(). 【F:src/Layouts/ChatbotLayout/TopBarActionButton.php†L14-L227】
- `Uxmal\Backoffice\Layouts\MasterLayout` Métodos fluentes: ->insertAfterBodyContent(), ->insertBeforeBodyContent(), ->setTopBarContent(), ->setTopBarActionButtons(), ->addTopBarActionButton(), ->setMenuLogo(). 【F:src/Layouts/MasterLayout.php†L15-L557】
- `Uxmal\Backoffice\Layouts\MasterLayout\TopBarActionButton` Métodos fluentes: ->fullscreen(), ->logout(), ->user(), ->dropdownWithIcon(), ->themeMode(), ->search(). 【F:src/Layouts/MasterLayout/TopBarActionButton.php†L15-L248】
- `Uxmal\Backoffice\Layouts\MasterWithoutNav` Métodos fluentes: ->buildHead(), ->buildBody(), ->build(), ->toHtml(). 【F:src/Layouts/MasterWithoutNav.php†L7-L90】
- `Uxmal\Backoffice\Layouts\SimpleLayout` Métodos fluentes: ->buildHead(), ->buildBody(), ->build(), ->toHtml(). 【F:src/Layouts/SimpleLayout.php†L8-L114】

### Middleware
- `Uxmal\Backoffice\Middleware\CheckEnabledServicesMiddleware` Métodos fluentes: ->handle(). 【F:src/Middleware/CheckEnabledServicesMiddleware.php†L9-L35】
- `Uxmal\Backoffice\Middleware\MasterLayoutMiddleware` Métodos fluentes: ->handle(). 【F:src/Middleware/MasterLayoutMiddleware.php†L12-L50】
- `Uxmal\Backoffice\Middleware\RegisterDestinationsMiddleware` Métodos fluentes: ->handle(). 【F:src/Middleware/RegisterDestinationsMiddleware.php†L11-L31】

### Modelos
- `Uxmal\Backoffice\Models\ApplicationNodes` 【F:src/Models/ApplicationNodes.php†L9-L14】

### Páginas
- `Uxmal\Backoffice\Pages\LoginPage` 【F:src/Pages/LoginPage.php†L11-L154】

### Query Helper
- `Uxmal\Backoffice\Query` 【F:src/Query.php†L7-L27】

### Resource Manager
- `Uxmal\Backoffice\UxmalBackofficeUIResourceManager` Métodos fluentes: ->getInstance(), ->addJSResource(), ->getJSResources(), ->clearJSResources(), ->addCSSResource(), ->getCSSResources(). 【F:src/UxmalBackofficeUIResourceManager.php†L11-L349】

### Service Provider
- `Uxmal\Backoffice\UxmalBackofficeUIServiceProvider` Métodos fluentes: ->register(), ->boot(), ->loadRoutesFromDirectory(). 【F:src/UxmalBackofficeUIServiceProvider.php†L31-L160】

### Servicios Singleton
- `Uxmal\Backoffice\Services\FormsCollection` Métodos fluentes: ->getInstance(), ->add(), ->get(), ->getAll(), ->remove(). 【F:src/Services/FormsCollection.php†L8-L56】
- `Uxmal\Backoffice\Services\ModalCollection` Métodos fluentes: ->addModal(), ->hasModals(), ->getModals(). 【F:src/Services/ModalCollection.php†L5-L46】
- `Uxmal\Backoffice\Services\OffcanvasCollection` Métodos fluentes: ->addOffcanvas(), ->hasOffcanvas(), ->getOffcanvas(). 【F:src/Services/OffcanvasCollection.php†L5-L29】
- `Uxmal\Backoffice\Services\RegisteredDestinations` Métodos fluentes: ->add(), ->get(), ->set(). 【F:src/Services/RegisteredDestinations.php†L7-L33】
- `Uxmal\Backoffice\Services\RegisteredEndpoints` Métodos fluentes: ->add(), ->get(). 【F:src/Services/RegisteredEndpoints.php†L7-L24】
- `Uxmal\Backoffice\Services\RegisteredRoutes` Métodos fluentes: ->add(), ->get(). 【F:src/Services/RegisteredRoutes.php†L7-L24】

### Soporte base
- `Uxmal\Backoffice\Support\HtmlElement` Métodos fluentes: ->attribute(), ->disabled(), ->id(), ->uxmalId(), ->name(), ->data(). 【F:src/Support/HtmlElement.php†L26-L596】

