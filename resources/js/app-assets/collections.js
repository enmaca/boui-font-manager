import bouiFontManagerCollections from '../collections.js';

// Initialize Collections Manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Wait for BOUI to be available
    if (typeof boui !== 'undefined') {
        bouiFontManagerCollections(boui);
    } else {
        // Wait for BOUI to load
        window.addEventListener('load', () => {
            if (typeof boui !== 'undefined') {
                bouiFontManagerCollections(boui);
            }
        });
    }
});