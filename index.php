<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.2/marked.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <script>hljs.highlightAll();</script>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">AI Chatbot</h2>
                <span class="px-4 py-1.5 bg-green-100 text-green-800 rounded-full text-sm font-medium">Tested with Llama 3.2</span>
            </div>
        </div>
        
        <div class="chat-messages">
            <div id="chatbox" class="scroll-smooth"></div>
            
            <div id="typing" class="typing-indicator hidden">
                <div class="flex items-center gap-1.5 text-gray-500">
                    <span class="text-sm font-medium">AI is typing</span>
                    <span class="typing-dot inline-block w-1.5 h-1.5 bg-gray-500 rounded-full" style="--delay: 0"></span>
                    <span class="typing-dot inline-block w-1.5 h-1.5 bg-gray-500 rounded-full" style="--delay: 1"></span>
                    <span class="typing-dot inline-block w-1.5 h-1.5 bg-gray-500 rounded-full" style="--delay: 2"></span>
                </div>
            </div>
        </div>
        
        <div class="chat-input">
            <div class="flex gap-3 max-w-6xl mx-auto">
                <div class="flex-1 relative">
                    <textarea id="userInput" 
                            class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-auto"
                            placeholder="Type text... (Shift + Enter for new line)"
                            rows="2"
                            style="min-height: 60px; max-height: 130px;"
                    ></textarea>
                </div>
                <button id="sendBtn" 
                        onclick="sendMessage()"
                        class="px-6 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 active:bg-blue-700 transition-all duration-200 flex items-center gap-2 shadow-lg shadow-blue-500/30">
                    <span>SEND</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="footer text-center text-sm text-gray-600 mt-4">
            <p>
                Please visit <a href="https://github.com/aunhelloworld/ollama-chatbot-web" target="_blank" class="text-blue-600 hover:text-blue-800 transition duration-200">Aunhelloworld</a>
            </p>
        </div>
    </div>

    <script>
        marked.setOptions({
            breaks: true,
            gfm: true,
            headerIds: false,
            mangle: false
        });

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function createMessageElement(content, isUser) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `mb-4 ${isUser ? 'text-right' : 'text-left'}`;
            
            const innerDiv = document.createElement('div');
            innerDiv.className = `inline-block max-w-[80%] p-4 ${isUser ? 
                'message-bubble user' : 
                'message-bubble ai message-content'}`;
            
            if (isUser) {
                innerDiv.innerHTML = content.replace(/\n/g, '<br>');
            } else {
                const codeBlocks = [];
                content = content.replace(/```(html|HTML)?\n([\s\S]*?)```/g, (match, lang, code) => {
                    codeBlocks.push({ 
                        language: 'html', 
                        code: escapeHtml(code.trim())
                    });
                    return `§CODE${codeBlocks.length - 1}§`;
                });

                content = content.replace(/```(\w+)?\n([\s\S]*?)```/g, (match, lang, code) => {
                    codeBlocks.push({ 
                        language: lang || '', 
                        code: code.trim()
                    });
                    return `§CODE${codeBlocks.length - 1}§`;
                });

                let html = marked.parse(content);

                html = html.replace(/§CODE(\d+)§/g, (match, index) => {
                    const { language, code } = codeBlocks[parseInt(index)];
                    return `<pre><code class="language-${language}">${code}</code></pre>`;
                });

                innerDiv.innerHTML = html;
                innerDiv.querySelectorAll('pre code').forEach(block => {
                    hljs.highlightElement(block);
                });
            }
            
            messageDiv.appendChild(innerDiv);
            return messageDiv;
        }

        const textarea = document.getElementById('userInput');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 150) + 'px';
        });

        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                if (e.shiftKey) {
                    return;
                } else {
                    e.preventDefault();
                    sendMessage();
                }
            }
        });

        function sendMessage() {
            let userInput = document.getElementById("userInput");
            let message = userInput.value.trim();
            if (!message) return;

            userInput.value = "";
            userInput.style.height = 'auto';
            
            let chatbox = document.getElementById("chatbox");
            chatbox.appendChild(createMessageElement(message, true));
            chatbox.scrollTop = chatbox.scrollHeight;
            
            document.getElementById("typing").classList.remove("hidden");
            
            fetch("chat.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("typing").classList.add("hidden");
                
                if (data.response) {
                    chatbox.appendChild(createMessageElement(data.response, false));
                    chatbox.scrollTop = chatbox.scrollHeight;
                }
            })
            .catch(error => {
                console.error("Error:", error);
                document.getElementById("typing").classList.add("hidden");
                const errorMessage = createMessageElement("Sorry, there was an error connecting. Please try again.", false);
                errorMessage.querySelector('div').classList.add('text-red-600');
                chatbox.appendChild(errorMessage);
                chatbox.scrollTop = chatbox.scrollHeight;
            });
        }
    </script>
</body>
</html>
