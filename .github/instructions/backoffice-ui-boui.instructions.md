# Instrucciones de Copilot para usuarios de backoffice-ui BOUI

Estas instrucciones están diseñadas para ayudar a GitHub Copilot a generar código apropiado cuando trabajas en proyectos Laravel que utilizan la librería `uxmaltech/backoffice-ui`. Se enfocan en patrones de uso desde la perspectiva del consumidor de la librería.

## Contexto de uso

BOUI es un sistema de componentes UI reactivos para Laravel que combina PHP server-side con JavaScript client-side. Como usuario de la librería, interactúas principalmente con:

- **El objeto global `boui`** para acceder a componentes y estado
- **Atributos de datos HTML** para configurar componentes
- **Sistema de eventos** para comunicación entre componentes
- **API CBQ** para peticiones al backend

## 1. Inicialización y espera de componentes

### Patrón básico de inicialización

```javascript
// ✅ Correcto - Esperar a que BOUI esté listo
document.addEventListener('DOMContentLoaded', () => {
  // BOUI se inicializa automáticamente
  // Usar boui.waitFor() para componentes específicos
  boui.waitFor('myComponent').then(component => {
    // Trabajar con el componente
    component.setValue('initial value');
  });
});

// ✅ Correcto - Escuchar evento de inicialización
bouiState.onEvent('loaded.boui.app', () => {
  // BOUI completamente inicializado
  const myGrid = boui.get('userGrid');
  if (myGrid) {
    myGrid.refresh();
  }
});
```

### Inicialización de componentes dinámicos

```javascript
// ✅ Correcto - Después de insertar HTML dinámico
function addDynamicContent(htmlContent) {
  const container = document.getElementById('dynamic-container');
  container.innerHTML = htmlContent;
  
  // Re-inicializar componentes en el nuevo contenido
  boui.initializeComponents(container);
}

// ✅ Correcto - Después de respuestas Livewire
Livewire.on('content-updated', () => {
  // Re-inicializar componentes afectados
  boui.initializeComponents(document.getElementById('updated-section'));
});
```

## 2. Uso de componentes HTML

### Estructura básica de componentes

```html
<!-- ✅ Patrón básico de componente -->
<div 
  data-uxmal-ui-modal="confirmDialog"
  data-uxmal-id="modal-001"
  data-uxmal-path="content"
  data-options='{"size": "lg", "backdrop": "static"}'>
  
  <div class="modal-header">
    <h5>Confirmar Acción</h5>
  </div>
  <div class="modal-body">
    <p>¿Estás seguro de realizar esta acción?</p>
  </div>
</div>

<!-- ✅ Componente con relación padre-hijo -->
<div data-uxmal-path="content.users" data-uxmal-for="content">
  <div 
    data-uxmal-ui-gridjs="usersList"
    data-uxmal-path="content.users.grid"
    data-uxmal-for="content.users">
  </div>
</div>
```

### Formularios con validación

```html
<!-- ✅ Formulario BOUI con componentes -->
<form 
  data-uxmal-form="userForm"
  data-uxmal-path="content.form"
  data-uxmal-action-submit-form-action="/api/users">
  
  <!-- Input con validación -->
  <input 
    data-uxmal-form-input="userName"
    data-uxmal-path="content.form.userName"
    data-validation="required|string|min:3|max:50"
    type="text" 
    class="form-control">
  
  <!-- Botón de envío -->
  <button 
    data-uxmal-action="submit-form"
    data-uxmal-action-on="click"
    data-uxmal-action-target="userForm"
    type="button" 
    class="btn btn-primary">
    Guardar Usuario
  </button>
</form>
```

## 3. Sistema de acciones declarativas

### Acciones comunes

```html
<!-- ✅ Acción de dispatch para comunicación -->
<button 
  data-uxmal-action="dispatch"
  data-uxmal-action-on="click"
  data-uxmal-action-dispatch-event="user-selected"
  data-uxmal-action-dispatch-data='{"userId": 123, "action": "view"}'
  class="btn btn-info">
  Ver Usuario
</button>

<!-- ✅ Acción fetch para API -->
<button 
  data-uxmal-action="fetch"
  data-uxmal-action-on="click"
  data-uxmal-action-fetch-route="users.delete"
  data-uxmal-action-fetch-params='{"id": 123}'
  data-uxmal-action-fetch-confirm="¿Eliminar usuario?"
  class="btn btn-danger">
  Eliminar
</button>

<!-- ✅ Acción JavaScript segura -->
<button 
  data-uxmal-action="javascript"
  data-uxmal-action-on="click"
  data-uxmal-action-javascript-function="handleCustomLogic"
  class="btn btn-secondary">
  Lógica Personalizada
</button>
```

### JavaScript para acciones personalizadas

```javascript
// ✅ Función global para acciones JavaScript
window.handleCustomLogic = function(element, event) {
  // Acceso seguro a BOUI
  const userGrid = boui.get('usersList');
  if (userGrid) {
    userGrid.refresh();
  }
  
  // Logging con niveles apropiados
  boui.debug('Custom logic executed');
  
  // Dispatch de eventos para comunicación
  bouiState.dispatch('custom-action-completed', {
    elementId: element.getAttribute('data-uxmal-id'),
    timestamp: new Date().toISOString()
  });
};
```

## 4. Comunicación con APIs (CBQ)

### Configuración de rutas en componentes

```html
<!-- ✅ Componente con rutas CBQ configuradas -->
<div 
  data-uxmal-ui-gridjs="userGrid"
  data-uxmal-ui-gridjs-named-route="users.list"
  data-uxmal-ui-gridjs-named-route-parameters='{"page": 1, "limit": 10}'
  data-uxmal-ui-gridjs-named-route-method="GET">
</div>
```

### Uso de CBQ en JavaScript

```javascript
// ✅ Petición CBQ básica
async function loadUsers(filters = {}) {
  try {
    const response = await boui.cbq('users.list', {
      page: 1,
      limit: 10
    }, filters);
    
    // Actualizar UI con respuesta
    const userGrid = boui.get('userGrid');
    if (userGrid) {
      userGrid.updateData(response.data);
    }
    
    return response;
  } catch (error) {
    boui.error('Error loading users:', error);
    
    // Mostrar error al usuario
    boui.toast({
      type: 'error',
      message: 'Error al cargar usuarios'
    });
  }
}

// ✅ CBQ con validación de payload
async function createUser(userData) {
  // Definir reglas de validación
  const validationRules = {
    name: 'required|string|min:3|max:50',
    email: 'required|email',
    age: 'default:18|integer|min:1|max:120'
  };
  
  try {
    const response = await boui.cbq('users.store', {}, userData, {
      validation: validationRules
    });
    
    boui.toast({
      type: 'success',
      message: 'Usuario creado exitosamente'
    });
    
    return response;
  } catch (error) {
    if (error.response?.status === 422) {
      // Errores de validación
      const errors = error.response.data.errors;
      Object.keys(errors).forEach(field => {
        const input = boui.get(field);
        if (input) {
          input.showErrorMessage(errors[field][0]);
        }
      });
    }
  }
}
```

## 5. Gestión de eventos y estado

### Escuchar eventos del sistema

```javascript
// ✅ Eventos del ciclo de vida de componentes
bouiState.onEvent('ui-modal.boui.shown', (data) => {
  console.log(`Modal ${data.name} mostrado`);
});

bouiState.onEvent('ui-gridjs.boui.row-selected', (data) => {
  console.log(`Fila seleccionada:`, data);
});

// ✅ Eventos personalizados de aplicación
bouiState.onEvent('user-selected', (userData) => {
  // Actualizar otros componentes
  const detailPanel = boui.get('userDetail');
  if (detailPanel) {
    detailPanel.loadUser(userData.userId);
  }
});
```

### Comunicación entre componentes

```javascript
// ✅ Patrón de comunicación mediante eventos
class UserComponent {
  selectUser(userId) {
    // Dispatch evento para otros componentes
    bouiState.dispatch('user-selected', {
      userId: userId,
      timestamp: Date.now(),
      source: this.name
    });
  }
  
  onUserDeleted(userId) {
    // Notificar eliminación
    bouiState.dispatch('user-deleted', { userId });
    
    // Actualizar UI local
    this.removeUserFromList(userId);
    
    // Mostrar confirmación
    boui.toast({
      type: 'success',
      message: 'Usuario eliminado'
    });
  }
}
```

## 6. Componentes dinámicos y modales

### Creación de modales dinámicos

```javascript
// ✅ Crear modal dinámico
async function showConfirmDialog(message, onConfirm) {
  const modal = await boui.createElement({
    type: 'ui-modal',
    name: 'confirmDialog',
    data: {
      title: 'Confirmación',
      body: message,
      size: 'sm',
      buttons: [
        {
          text: 'Cancelar',
          class: 'btn-secondary',
          dismiss: true
        },
        {
          text: 'Confirmar',
          class: 'btn-primary',
          onclick: onConfirm
        }
      ]
    }
  });
  
  modal.show();
  return modal;
}

// ✅ Uso del modal dinámico
function deleteUser(userId) {
  showConfirmDialog(
    '¿Estás seguro de eliminar este usuario?',
    async () => {
      try {
        await boui.cbq('users.delete', { id: userId });
        boui.toast({
          type: 'success',
          message: 'Usuario eliminado'
        });
        
        // Actualizar lista
        const userGrid = boui.get('userGrid');
        if (userGrid) {
          userGrid.refresh();
        }
      } catch (error) {
        boui.error('Error deleting user:', error);
      }
    }
  );
}
```

## 7. Formularios y validación

### Manejo de formularios con BOUI

```javascript
// ✅ Clase para manejar formulario de usuario
class UserFormHandler {
  constructor(formName) {
    this.form = boui.get(formName);
    this.setupValidation();
    this.bindEvents();
  }
  
  setupValidation() {
    // Reglas de validación del cliente
    this.validationRules = {
      name: 'required|string|min:3|max:50',
      email: 'required|email',
      phone: 'nullable|string|regex:/^[+]?[0-9\\s-()]+$/',
      birthDate: 'nullable|date|before:today'
    };
  }
  
  bindEvents() {
    // Validación en tiempo real
    const nameInput = boui.get('userName');
    if (nameInput) {
      nameInput.on('blur', this.validateField.bind(this, 'name'), 300);
    }
    
    // Escuchar envío del formulario
    bouiState.onEvent('form-submitted', (data) => {
      if (data.formName === this.form.name) {
        this.handleSubmission(data);
      }
    });
  }
  
  validateField(fieldName, event) {
    const value = event.target.value;
    const rules = this.validationRules[fieldName];
    
    if (rules) {
      // Validación básica del cliente
      if (rules.includes('required') && !value.trim()) {
        this.showFieldError(fieldName, 'Este campo es requerido');
        return false;
      }
      
      if (rules.includes('email') && value && !this.isValidEmail(value)) {
        this.showFieldError(fieldName, 'Email inválido');
        return false;
      }
      
      this.clearFieldError(fieldName);
      return true;
    }
  }
  
  async submit() {
    const formData = this.form.getFormData();
    
    try {
      const response = await boui.cbq('users.store', {}, formData, {
        validation: this.validationRules
      });
      
      boui.toast({
        type: 'success',
        message: 'Usuario guardado exitosamente'
      });
      
      // Limpiar formulario
      this.form.reset();
      
      return response;
    } catch (error) {
      this.handleValidationErrors(error);
    }
  }
  
  handleValidationErrors(error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      
      Object.keys(errors).forEach(field => {
        this.showFieldError(field, errors[field][0]);
      });
    } else {
      boui.toast({
        type: 'error',
        message: 'Error al guardar usuario'
      });
    }
  }
  
  showFieldError(fieldName, message) {
    const field = boui.get(fieldName);
    if (field && field.showErrorMessage) {
      field.showErrorMessage(message);
    }
  }
  
  clearFieldError(fieldName) {
    const field = boui.get(fieldName);
    if (field && field.clearError) {
      field.clearError();
    }
  }
  
  isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }
}

// ✅ Inicialización del manejador de formulario
document.addEventListener('DOMContentLoaded', () => {
  boui.waitFor('userForm').then(() => {
    new UserFormHandler('userForm');
  });
});
```

## 8. Utilidades y helpers

### Uso de utilidades BOUI

```javascript
// ✅ Usar utilidades incluidas en BOUI
import { debounce } from '/vendor/uxmaltech/backoffice-ui/resources/js/boui/utils/function/debounce.js';
import { wait } from '/vendor/uxmaltech/backoffice-ui/resources/js/boui/utils/wait.js';
import { generateId } from '/vendor/uxmaltech/backoffice-ui/resources/js/boui/utils/generateId.js';

// Búsqueda con debounce
const searchInput = boui.get('searchInput');
if (searchInput) {
  const debouncedSearch = debounce(async (query) => {
    if (query.length >= 2) {
      const results = await boui.cbq('search.users', {}, { query });
      updateSearchResults(results);
    }
  }, 300);
  
  searchInput.on('input', (e) => debouncedSearch(e.target.value));
}

// Espera artificial para UX
async function showLoadingState() {
  const loader = boui.get('loadingSpinner');
  loader?.show();
  
  await wait(500); // Mínimo tiempo de loading
  
  loader?.hide();
}

// Generar IDs únicos para elementos dinámicos
function createDynamicElement() {
  const uniqueId = generateId();
  const element = document.createElement('div');
  element.setAttribute('data-uxmal-id', uniqueId);
  element.setAttribute('data-uxmal-ui-card', `dynamic-card-${uniqueId}`);
  
  return element;
}
```

## 9. Debugging y logging

### Configuración de logging para desarrollo

```javascript
// ✅ Configurar logging para desarrollo
if (window.location.hostname === 'localhost') {
  boui.logLevel('DEBUG');
  
  // Logging de eventos para debugging
  bouiState.onEvent(/.*/, (eventName, data) => {
    boui.debug(`Event: ${eventName}`, data);
  });
}

// ✅ Logging estructurado en funciones
async function performComplexOperation() {
  boui.info('Starting complex operation');
  
  try {
    boui.debug('Step 1: Validating input');
    // ... validación
    
    boui.debug('Step 2: Making API call');
    const result = await boui.cbq('complex.operation', {}, {});
    
    boui.info('Complex operation completed successfully');
    return result;
  } catch (error) {
    boui.error('Complex operation failed:', error);
    throw error;
  }
}
```

## 10. Mejores prácticas para usuarios

### Patrones recomendados

```javascript
// ✅ Patrón de inicialización segura
class MyPageController {
  constructor() {
    this.init();
  }
  
  async init() {
    // Esperar a que BOUI esté listo
    await new Promise(resolve => {
      if (window.boui) {
        resolve();
      } else {
        document.addEventListener('DOMContentLoaded', resolve);
      }
    });
    
    // Esperar componentes específicos
    this.userGrid = await boui.waitFor('userGrid');
    this.userForm = await boui.waitFor('userForm');
    
    this.bindEvents();
    this.loadInitialData();
  }
  
  bindEvents() {
    // Eventos específicos de la página
    bouiState.onEvent('user-selected', this.onUserSelected.bind(this));
    bouiState.onEvent('user-deleted', this.onUserDeleted.bind(this));
  }
  
  async loadInitialData() {
    try {
      const users = await boui.cbq('users.list');
      this.userGrid.updateData(users.data);
    } catch (error) {
      boui.error('Error loading initial data:', error);
    }
  }
  
  onUserSelected(userData) {
    // Manejar selección de usuario
  }
  
  onUserDeleted(userData) {
    // Manejar eliminación de usuario
    this.userGrid.refresh();
  }
}

// Inicialización de la página
new MyPageController();
```

### Anti-patrones a evitar

```javascript
// ❌ NO hacer - Acceso directo sin esperar inicialización
const grid = boui.get('userGrid'); // Puede ser null
grid.refresh(); // Error si grid es null

// ✅ Correcto - Esperar inicialización
boui.waitFor('userGrid').then(grid => {
  grid.refresh();
});

// ❌ NO hacer - Peticiones directas con fetch/axios
fetch('/api/users')
  .then(response => response.json())
  .then(data => {
    // Sin manejo de CSRF, auth, etc.
  });

// ✅ Correcto - Usar CBQ
boui.cbq('users.list').then(data => {
  // Manejo automático de auth, CSRF, errores
});

// ❌ NO hacer - Logging manual
console.log('Debug info');

// ✅ Correcto - Usar sistema de logging BOUI
boui.debug('Debug info');
```
