// Configure marked.js
        marked.setOptions({
            breaks: true,
            gfm: true,
            headerIds: false,
            mangle: false,
            sanitize: false, // We'll use DOMPurify instead
            highlight: function(code, lang) {
                if (lang && hljs.getLanguage(lang)) {
                    try {
                        return hljs.highlight(code, { language: lang }).value;
                    } catch (err) {}
                }
                return code;
            }
        });

        // Custom renderer for marked
        const renderer = new marked.Renderer();
        
        // Enhanced link rendering with security
        renderer.link = (href, title, text) => {
            if (!href.startsWith('http')) return text;
            return `<a href="${href}" title="${title || ''}" target="_blank" rel="noopener noreferrer">${text}</a>`;
        };

        // Enhanced image rendering with error handling
        renderer.image = (href, title, text) => {
            return `<img src="${href}" alt="${text}" title="${title || ''}" onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Crect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\' ry=\'2\'/%3E%3Ccircle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/%3E%3Cpolyline points=\'21 15 16 10 5 21\'/%3E%3C/svg%3E';this.classList.add('error');" class="max-w-full h-auto rounded-lg shadow-sm">`;
        };

        marked.setOptions({ renderer });

        // Content sanitization function
        function sanitizeContent(content) {
            return DOMPurify.sanitize(content, {
                ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'u', 'code', 'pre', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'hr', 'div', 'span'],
                ALLOWED_ATTR: ['href', 'target', 'rel', 'src', 'alt', 'class', 'title', 'style'],
                ALLOWED_URI_REGEXP: /^(?:(?:(?:f|ht)tps?|mailto|tel|callto|data|file|image):|[^a-z]|[a-z+.\-]+(?:[^a-z+.\-:]|$))/i
            });
        }
        
        function detectAndParseJSON(text) {
            try {
                // Look for JSON-like content
                const jsonMatch = text.match(/\{[\s\S]*\}/);
                if (jsonMatch) {
                    const jsonContent = jsonMatch[0];
                    const data = JSON.parse(jsonContent);
                    
                    // Handle price table specifically
                    if (data.price_table) {
                        const tableHtml = createPriceTable(data.price_table);
                        return text.replace(jsonContent, tableHtml);
                    }
                    
                    // Handle other JSON data
                    if (typeof data === 'object') {
                        const tableHtml = createGenericTable(data);
                        return text.replace(jsonContent, tableHtml);
                    }
                }
            } catch (e) {
                console.log('Not valid JSON, returning original text');
            }
            return text;
        }

        // Create price table HTML
        function createPriceTable(data) {
            let html = '<table class="chat-table">';
            html += '<thead><tr><th>Item</th><th>Price</th></tr></thead><tbody>';
            
            for (const [item, details] of Object.entries(data)) {
                html += `<tr>
                    <td>${item}</td>
                    <td>${details.price}</td>
                </tr>`;
            }
            
            html += '</tbody></table>';
            return html;
        }

        // Create generic table for other JSON data
        function createGenericTable(data) {
            if (Array.isArray(data)) {
                return createArrayTable(data);
            }
            
            let html = '<table class="chat-table">';
            html += '<thead><tr><th>Key</th><th>Value</th></tr></thead><tbody>';
            
            for (const [key, value] of Object.entries(data)) {
                const displayValue = typeof value === 'object' ? 
                    JSON.stringify(value, null, 2) : value;
                html += `<tr>
                    <td>${key}</td>
                    <td>${displayValue}</td>
                </tr>`;
            }
            
            html += '</tbody></table>';
            return html;
        }

        // Create table for array data
        function createArrayTable(data) {
            if (!data.length) return '';
            
            const headers = Object.keys(data[0]);
            let html = '<table class="chat-table"><thead><tr>';
            
            headers.forEach(header => {
                html += `<th>${header}</th>`;
            });
            
            html += '</tr></thead><tbody>';
            
            data.forEach(item => {
                html += '<tr>';
                headers.forEach(header => {
                    html += `<td>${item[header]}</td>`;
                });
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            return html;
        }

        // Create message element with enhanced error handling
        function createMessageElement(content, isUser) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `mb-4 ${isUser ? 'text-right' : 'text-left'}`;
            
            const innerDiv = document.createElement('div');
            innerDiv.className = `message-bubble ${isUser ? 'user' : 'ai message-content'}`;

            try {
                if (isUser) {
                    innerDiv.innerHTML = sanitizeContent(content.replace(/\n/g, '<br>'));
                } else {
                    // Process JSON before Markdown
                    content = detectAndParseJSON(content);
                    const parsedContent = marked.parse(content);
                    innerDiv.innerHTML = sanitizeContent(parsedContent);
                    
                    // Apply syntax highlighting
                    innerDiv.querySelectorAll('pre code').forEach(block => {
                        hljs.highlightElement(block);
                    });
                }
            } catch (error) {
                console.error('Message processing error:', error);
                innerDiv.innerHTML = `<div class="error-message">
                    Error processing message. Please try again.
                    <br><small>${error.message}</small>
                </div>`;
            }
            
            messageDiv.appendChild(innerDiv);
            return messageDiv;
        }

        // Auto-resize textarea
        const textarea = document.getElementById('userInput');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 130) + 'px';
        });

        // Handle Enter key
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Enhanced message sending with error handling
        async function sendMessage() {
            const userInput = document.getElementById('userInput');
            const message = userInput.value.trim();
            if (!message) return;

            userInput.value = '';
            userInput.style.height = 'auto';
            
            const chatbox = document.getElementById('chatbox');
            chatbox.appendChild(createMessageElement(message, true));
            chatbox.scrollTop = chatbox.scrollHeight;
            
            const typingIndicator = document.getElementById('typing');
            
            try {
                typingIndicator.classList.remove('hidden');
                
                const response = await fetch('chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: message })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                typingIndicator.classList.add('hidden');
                
                if (data.response) {
                    // Add small delay to simulate natural conversation flow
                    setTimeout(() => {
                        chatbox.appendChild(createMessageElement(data.response, false));
                        chatbox.scrollTop = chatbox.scrollHeight;
                    }, 300);
                } else {
                    throw new Error('Empty response from server');
                }
            } catch (error) {
                console.error('Error:', error);
                typingIndicator.classList.add('hidden');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'mb-4 text-left';
                errorDiv.innerHTML = `
                    <div class="inline-block message-bubble ai error-message">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>Sorry, there was an error: ${error.message}</span>
                        </div>
                        <div class="text-sm mt-1">Please try again or refresh the page.</div>
                    </div>`;
                chatbox.appendChild(errorDiv);
                chatbox.scrollTop = chatbox.scrollHeight;
            }
        }

        // Initialize highlight.js
        document.addEventListener('DOMContentLoaded', function() {
            hljs.configure({
                languages: ['javascript', 'python', 'php', 'html', 'css', 'xml', 'json'],
                ignoreUnescapedHTML: true
            });
        });

        // Add support for copying code blocks
        document.addEventListener('click', function(e) {
            if (e.target && e.target.matches('pre code')) {
                const code = e.target.textContent;
                navigator.clipboard.writeText(code).then(() => {
                    // Show copy feedback
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-fade-in-out';
                    notification.textContent = 'Code copied to clipboard!';
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                    }, 2000);
                }).catch(err => console.error('Failed to copy text:', err));
            }
        });

        // Handle paste events for file uploads (if needed)
        textarea.addEventListener('paste', function(e) {
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            
            for (const item of items) {
                if (item.type.indexOf('image') === 0) {
                    e.preventDefault();
                    
                    // Handle image paste here if needed
                    // You can add image upload functionality
                    
                    break;
                }
            }
        });
