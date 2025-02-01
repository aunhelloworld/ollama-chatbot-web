<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced AI Chatbot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.2/marked.min.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">AI Chatbot</h2>
                <span class="px-4 py-1.5 bg-green-100 text-green-800 rounded-full text-sm font-medium">Llama 3.2</span>
            </div>
        </div>
        
        <div class="chat-messages">
            <div id="chatbox" class="scroll-smooth"></div>
            
            <div id="typing" class="typing-indicator hidden">
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-6 h-6">
                        <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-small text-gray-600">AI is thinking...</span>
                </div>
            </div>
        </div>
        
        <div class="chat-input">
            <div class="flex gap-3 max-w-6xl mx-auto">
                <div class="flex-1 relative">
                    <textarea id="userInput" 
                            class="w-full p-4 border border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none overflow-auto"
                            placeholder="Type your message... (Shift + Enter for new line)"
                            rows="2"
                            style="min-height: 60px; max-height: 130px;"
                    ></textarea>
                </div>
                <button id="sendBtn" 
                        onclick="sendMessage()"
                        class="px-6 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 active:bg-blue-700 transition-all duration-200 flex items-center gap-2 shadow-lg shadow-blue-500/30">
                    <span>Send</span>
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
    <script src="script.js"></script>
</body>
</html>
