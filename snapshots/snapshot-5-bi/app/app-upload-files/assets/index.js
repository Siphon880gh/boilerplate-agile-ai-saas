document.addEventListener('DOMContentLoaded', () => {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const selectFiles = document.getElementById('selectFiles');
    const previewGrid = document.getElementById('previewGrid');
    const submit = document.getElementById('submit');
    const urlInput = document.getElementById('urlInput');
    const addUrlButton = document.getElementById('addUrl');

    window.files = [];

    // Helper function to escape HTML and prevent XSS
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Ensure only one event listener is added
    selectFiles.addEventListener('click', () => {
        fileInput.click();
    });

    // Drag and drop functionality
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('dragover');
        
        // Handle both files and other dragged items
        const items = e.dataTransfer.items;
        if (items) {
            // Use DataTransferItemList interface to access the files
            Array.from(items).forEach(item => {
                if (item.kind === 'file') {
                    const file = item.getAsFile();
                    handleFiles([file]); // Pass as array to reuse handleFiles function
                }
            });
        } else {
            // Use DataTransfer interface to access the files
            handleFiles(e.dataTransfer.files);
        }
    });

    fileInput.addEventListener('change', (e) => {
        const filesLength = e.target.files.length;
        if(filesLength > 0) {
            handleFiles(e.target.files);
            submit.classList.remove("disabled");
        }
    });

    // Add URL functionality
    addUrlButton.addEventListener('click', handleUrlAdd);
    urlInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleUrlAdd();
        }
    });

    function handleFiles(newFiles) {
        console.log('Handling files:', newFiles); // Debug log
        Array.from(newFiles).forEach(file => {
            const fileType = getFileType(file);
            console.log('File:', file.name, 'Type:', file.type, 'Detected as:', fileType); // Debug log
            
            // Accept all supported file types - add excel and csv
            if (['image', 'audio', 'video', 'txt', 'text', 'pdf', 'doc', 'excel', 'csv', 'json', 'xml'].includes(fileType)) {
                window.files.push(file);
                displayPreview(file);
            } else {
                alert(`File type not supported: ${file.name}`);
            }
        });
    }

    function handleUrlAdd() {
        let url = urlInput.value.trim();
        if (!url) {
            alert('Please enter a valid URL');
            return;
        }

        // Basic URL validation
        if(url.indexOf('http') !== 0){
            url = 'https://' + url;
        }
        try {
            new URL(url);
        } catch (e) {
            alert('Please enter a valid URL');
            return;
        }

        // Create a text file containing the URL
        // const urlText = `URL: ${url}\nAdded: ${new Date().toLocaleString()}`;
        const urlText = `URL: ${url}`;
        const urlBlob = new Blob([urlText], { type: 'text/plain' });
        
        // Create a File object from the Blob
        const urlFile = new File([urlBlob], `url-${Date.now()}.txt`, {
            type: 'text/plain',
            lastModified: new Date()
        });

        window.files.push(urlFile);
        displayPreview(urlFile);
        urlInput.value = ''; // Clear the input

        if (window.files.length > 0) {
            submit.classList.remove("disabled");
        }
    }

    function getFileType(file) {
        const mimeType = file.type.toLowerCase();
        const fileName = file.name.toLowerCase();
        
        console.log('Checking file:', { name: fileName, type: mimeType }); // Debug log
        
        // Image types - add HEIC detection
        if (mimeType.startsWith('image/') || 
            fileName.endsWith('.heic') || 
            fileName.endsWith('.heif')) return 'image';
        
        // Video types
        if (mimeType.startsWith('video/') || 
            fileName.endsWith('.mp4') || 
            fileName.endsWith('.webm') || 
            fileName.endsWith('.mov')) return 'video';
        
        // Audio types
        if (mimeType.startsWith('audio/') || 
            fileName.endsWith('.mp3') || 
            fileName.endsWith('.wav') || 
            fileName.endsWith('.ogg') || 
            fileName.endsWith('.m4a')) return 'audio';
        
        // Excel files
        if (mimeType === 'application/vnd.ms-excel' || 
            mimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
            fileName.endsWith('.xlsx') || 
            fileName.endsWith('.xls') ||
            fileName.endsWith('.xlsm') ||
            fileName.endsWith('.xlsb')) return 'excel';
        
        // CSV files
        if (mimeType === 'text/csv' ||
            fileName.endsWith('.csv')) return 'csv';
        
        // JSON files
        if (mimeType === 'application/json' ||
            fileName.endsWith('.json')) return 'json';
        
        // XML files
        if (mimeType === 'application/xml' ||
            mimeType === 'text/xml' ||
            fileName.endsWith('.xml')) return 'xml';
        
        // Text files
        if (mimeType === 'text/plain' || 
            fileName.endsWith('.txt') || 
            fileName.endsWith('.text')) return 'text';
        
        // PDF files
        if (mimeType === 'application/pdf' || 
            fileName.endsWith('.pdf')) return 'pdf';
        
        // Word documents
        if (mimeType === 'application/msword' || 
            mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ||
            fileName.endsWith('.doc') || 
            fileName.endsWith('.docx')) return 'doc';
        
        // If none of the above, treat as misc
        return 'misc';
    }

    function displayPreview(file) {
        const fileType = getFileType(file);
        const previewItem = document.createElement('div');
        previewItem.className = 'preview-item';
        previewItem.setAttribute('data-type', fileType);

        if (file.name.startsWith('url-') && fileType === 'text') {
            // Read the URL from the text file
            const reader = new FileReader();
            reader.onload = (e) => {
                const content = e.target.result;
                const url = content.split('\n')[0].replace('URL: ', '');
                previewItem.innerHTML = `
                    <div class="file-icon url"></div>
                    <a href="${url}" target="_blank" class="filename" title="${url}">${url}</a>
                    <button class="remove">&times;</button>
                `;
                addRemoveHandler(previewItem, file);
            };
            reader.readAsText(file);
            previewGrid.appendChild(previewItem);
            return;
        }

        if (fileType === 'image') {
            const fileName = file.name.toLowerCase();
            if (fileName.endsWith('.heic') || fileName.endsWith('.heif')) {
                // For HEIC files, show a loading state first
                previewItem.innerHTML = `
                    <div class="file-icon image"></div>
                    <p class="filename">${file.name}</p>
                    <p class="loading">Converting image...</p>
                    <button class="remove">&times;</button>
                `;
                
                // Convert HEIC to JPEG using heic2any library
                heic2any({
                    blob: file,
                    toType: "image/jpeg",
                    quality: 0.8
                })
                .then((jpegBlob) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <p class="filename">${file.name}</p>
                            <button class="remove">&times;</button>
                        `;
                        addRemoveHandler(previewItem, file);
                    };
                    reader.readAsDataURL(jpegBlob);
                })
                .catch((error) => {
                    console.error('Error converting HEIC:', error);
                    previewItem.innerHTML = `
                        <div class="file-icon image"></div>
                        <p class="filename">${file.name}</p>
                        <p class="error">Failed to convert image</p>
                        <button class="remove">&times;</button>
                    `;
                    addRemoveHandler(previewItem, file);
                });
            } else {
                // Handle other image types as before
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <p class="filename">${file.name}</p>
                        <button class="remove">&times;</button>
                    `;
                    addRemoveHandler(previewItem, file);
                };
                reader.readAsDataURL(file);
            }
            previewGrid.appendChild(previewItem);
            return;
        }

        switch (fileType) {
            case 'audio':
                previewItem.classList.add('with-player');
                const audioReader = new FileReader();
                audioReader.onload = (e) => {
                    previewItem.innerHTML = `
                        <audio controls>
                            <source src="${e.target.result}" type="${file.type}">
                            Your browser does not support the audio element.
                        </audio>
                        <p class="filename">${file.name}</p>
                        <button class="remove">&times;</button>
                    `;
                    addRemoveHandler(previewItem, file);
                };
                audioReader.readAsDataURL(file);
                break;

            case 'video':
                previewItem.classList.add('with-player');
                const videoReader = new FileReader();
                videoReader.onload = (e) => {
                    previewItem.innerHTML = `
                        <video controls>
                            <source src="${e.target.result}" type="${file.type}">
                            Your browser does not support the video element.
                        </video>
                        <p class="filename">${file.name}</p>
                        <button class="remove">&times;</button>
                    `;
                    addRemoveHandler(previewItem, file);
                };
                videoReader.readAsDataURL(file);
                break;

            case 'text':
                previewItem.classList.add('with-text-preview');
                const textReader = new FileReader();
                textReader.onload = (e) => {
                    const textContent = e.target.result;
                    previewItem.innerHTML = `
                        <div class="text-preview">${escapeHtml(textContent.slice(0, 500))}${textContent.length > 500 ? '...' : ''}</div>
                        <p class="filename">${file.name}</p>
                        <button class="remove">&times;</button>
                    `;
                    addRemoveHandler(previewItem, file);
                };
                textReader.readAsText(file);
                break;

            case 'pdf':
                previewItem.classList.add('with-pdf-preview');
                const pdfReader = new FileReader();
                pdfReader.onload = async (e) => {
                    try {
                        const typedarray = new Uint8Array(e.target.result);
                        const pdf = await pdfjsLib.getDocument(typedarray).promise;
                        const page = await pdf.getPage(1);
                        const viewport = page.getViewport({ scale: 0.5 });
                        
                        const canvas = document.createElement('canvas');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        
                        await page.render({
                            canvasContext: canvas.getContext('2d'),
                            viewport: viewport
                        }).promise;

                        previewItem.innerHTML = `
                            <div class="pdf-preview">
                                ${canvas.outerHTML}
                                <span class="page-count">Pages: ${pdf.numPages}</span>
                            </div>
                            <p class="filename">${file.name}</p>
                            <button class="remove">&times;</button>
                        `;
                        addRemoveHandler(previewItem, file);
                    } catch (error) {
                        console.error('Error previewing PDF:', error);
                        // Fallback to icon if preview fails
                        previewItem.innerHTML = `
                            <div class="file-icon pdf"></div>
                            <p class="filename">${file.name}</p>
                            <button class="remove">&times;</button>
                        `;
                        addRemoveHandler(previewItem, file);
                    }
                };
                pdfReader.readAsArrayBuffer(file);
                break;

            default:
                previewItem.innerHTML = `
                    <div class="file-icon ${fileType}"></div>
                    <p class="filename">${file.name}</p>
                    <button class="remove">&times;</button>
                `;
                addRemoveHandler(previewItem, file);
                break;
        }

        previewGrid.appendChild(previewItem);
    }

    function addRemoveHandler(previewItem, file) {
        previewItem.querySelector('.remove').addEventListener('click', () => {
            const confirmed = confirm('Are you sure you want to remove this file?');
            if (!confirmed) return;
            const index = window.files.indexOf(file);
            if (index > -1) {
                window.files.splice(index, 1);
                previewItem.remove();
                if (window.files.length === 0) {
                    submit.classList.add("disabled");
                }
            }
        });
    }
});