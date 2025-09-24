/**
 * Font Manager Collections
 * @param {BackofficeUI} boui - The BackofficeUI instance.
 */
const bouiFontManagerCollections = (boui) => {

    // Handle collection creation
    boui.on('click', (event) => {
        if (event.element.id === 'createCollectionBtn') {
            const form = boui.get('forms.addCollectionForm');
            const nameInput = boui.get('forms.addCollectionForm.collectionName');
            const descriptionInput = boui.get('forms.addCollectionForm.collectionDescription');
            
            if (!form || !nameInput) {
                boui.error('Form elements not found');
                return;
            }

            const name = nameInput.getValue();
            if (!name || name.trim().length === 0) {
                boui.toast({
                    type: 'error',
                    message: 'El nombre de la colección es requerido'
                });
                return;
            }

            const description = descriptionInput ? descriptionInput.getValue() : '';

            // Send request to create collection
            boui.cbq('cmd.font-manager.collections.create.v1', {}, {
                name: name.trim(),
                description: description.trim()
            }).then(response => {
                boui.toast({
                    type: 'success',
                    message: 'Colección creada exitosamente'
                });
                
                // Close modal and refresh grid
                const modal = boui.get('modals.addCollection');
                if (modal) {
                    modal.hide();
                }
                
                const grid = boui.get('content.collections');
                if (grid) {
                    grid.reload();
                }
                
                // Reset form
                form.reset();
            }).catch(error => {
                boui.error('Error creating collection:', error);
                boui.toast({
                    type: 'error',
                    message: 'Error al crear la colección'
                });
            });
        }
    });

    // Handle collection editing
    boui.on('click', (event) => {
        if (event.element.classList.contains('btnEditCollection')) {
            const collectionId = event.element.getAttribute('data-collection-id');
            const collectionName = event.element.getAttribute('data-collection-name');
            const collectionDescription = event.element.getAttribute('data-collection-description');

            // Create and open edit modal dynamically
            showEditCollectionModal(collectionId, collectionName, collectionDescription);
        }
    });

    // Handle collection deletion
    boui.on('click', (event) => {
        if (event.element.classList.contains('btnDeleteCollection')) {
            const collectionId = event.element.getAttribute('data-collection-id');
            const collectionName = event.element.getAttribute('data-collection-name');

            if (confirm(`¿Estás seguro de que deseas eliminar la colección "${collectionName}"?`)) {
                boui.cbq('cmd.font-manager.collections.delete.v1', {}, {
                    id: collectionId
                }).then(response => {
                    boui.toast({
                        type: 'success',
                        message: 'Colección eliminada exitosamente'
                    });
                    
                    const grid = boui.get('content.collections');
                    if (grid) {
                        grid.reload();
                    }
                }).catch(error => {
                    boui.error('Error deleting collection:', error);
                    boui.toast({
                        type: 'error',
                        message: 'Error al eliminar la colección'
                    });
                });
            }
        }
    });

    // Handle modal cleanup
    boui.on('ui-modal.boui.hidden', (event) => {
        switch (event.name) {
            case 'addCollection':
                const form = boui.get('forms.addCollectionForm');
                if (form) {
                    form.reset();
                }
                break;
            case 'editCollection':
                // Clean up dynamically created modal
                const modal = document.getElementById('editCollectionModal');
                if (modal) {
                    modal.remove();
                }
                break;
        }
    });

    /**
     * Show edit collection modal
     */
    function showEditCollectionModal(collectionId, currentName, currentDescription) {
        // Create modal HTML dynamically
        const modalHtml = `
            <div class="modal fade" id="editCollectionModal" tabindex="-1" aria-labelledby="editCollectionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editCollectionModalLabel">Editar Colección</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editCollectionForm">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="editCollectionName" class="form-label">Nombre de la colección</label>
                                        <input type="text" class="form-control" id="editCollectionName" value="${currentName}" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="editCollectionDescription" class="form-label">Descripción (opcional)</label>
                                        <textarea class="form-control" id="editCollectionDescription" rows="3">${currentDescription || ''}</textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="updateCollectionBtn">Actualizar Colección</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Append modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Initialize and show modal
        const modal = new bootstrap.Modal(document.getElementById('editCollectionModal'));
        modal.show();

        // Handle update button
        document.getElementById('updateCollectionBtn').addEventListener('click', () => {
            const name = document.getElementById('editCollectionName').value;
            const description = document.getElementById('editCollectionDescription').value;

            if (!name || name.trim().length === 0) {
                boui.toast({
                    type: 'error',
                    message: 'El nombre de la colección es requerido'
                });
                return;
            }

            boui.cbq('cmd.font-manager.collections.update.v1', {}, {
                id: collectionId,
                name: name.trim(),
                description: description.trim()
            }).then(response => {
                boui.toast({
                    type: 'success',
                    message: 'Colección actualizada exitosamente'
                });
                
                modal.hide();
                
                const grid = boui.get('content.collections');
                if (grid) {
                    grid.reload();
                }
            }).catch(error => {
                boui.error('Error updating collection:', error);
                boui.toast({
                    type: 'error',
                    message: 'Error al actualizar la colección'
                });
            });
        });
    }
}

export default bouiFontManagerCollections;