# Ollama AI Provider for Moodle

This plugin integrates Ollama AI models into Moodle's AI subsystem, allowing you to use local AI models for text generation and summarization throughout Moodle.

## Features

- **Text Generation**: Generate text using Ollama models
- **Text Summarization**: Automatically summarize content
- **Local AI**: Run AI models locally without sending data to external services
- **Privacy-Focused**: All processing happens on your own server

## Requirements

- Moodle 4.4 or later (AI subsystem required)
- Ollama server running locally or on your network
- PHP 7.4 or later with cURL extension

## Installation

### Step 1: Install Ollama

1. Download and install Ollama from https://ollama.ai
2. Start the Ollama service
3. Pull at least one model (e.g., `ollama pull llama2` or `ollama pull phi`)

### Step 2: Install the Plugin

**IMPORTANT**: This plugin must be moved from `local/ollama` to `ai/provider/ollama`

1. Move the plugin directory:
   ```
   From: /path/to/moodle/local/ollama
   To:   /path/to/moodle/ai/provider/ollama
   ```

2. Visit Site administration → Notifications to complete the installation

3. Configure the plugin at Site administration → Plugins → AI → Ollama

### Step 3: Configure the Plugin

1. Go to **Site administration → Plugins → AI → Ollama**
2. Set the **Ollama API Host** (default: `http://127.0.0.1:11434`)
3. Set the **Default Model** (e.g., `llama2`, `mistral`, `phi`)
4. Save changes

### Step 4: Enable AI Provider Instance

1. Go to **Site administration → AI → AI providers**
2. Create a new provider instance for Ollama
3. Configure which actions (generate_text, summarise_text) should use Ollama
4. Enable the provider instance

## Configuration

### API Host
The URL where your Ollama server is running. Common values:
- `http://127.0.0.1:11434` (local installation)
- `http://localhost:11434` (local installation)
- `http://your-server:11434` (remote server)

### Default Model
The Ollama model to use. Popular options:
- `llama2` - General purpose, good balance
- `mistral` - Fast and efficient
- `phi` - Lightweight, good for simple tasks
- `codellama` - Optimized for code generation

You can see available models by running `ollama list` on your server.

## Usage

Once configured, the Ollama provider will be available for:

1. **Text Editor AI Features**: Use AI-powered text generation in Moodle's text editor
2. **Content Summarization**: Automatically summarize course content
3. **Custom Integrations**: Any Moodle plugin that uses the AI subsystem

## Troubleshooting

### "Error processing request"

1. **Check Ollama is running**:
   ```bash
   curl http://127.0.0.1:11434/api/tags
   ```
   You should see a JSON response with available models.

2. **Verify the model exists**:
   ```bash
   ollama list
   ```
   Make sure the model you configured is in the list.

3. **Check Moodle can reach Ollama**:
   - Ensure there are no firewall rules blocking the connection
   - If Ollama is on a different server, verify network connectivity

### "No models available"

1. Pull at least one model:
   ```bash
   ollama pull llama2
   ```

2. Verify the model appears in `ollama list`

### Slow Response Times

- First requests can take 1-2 minutes as the model loads into memory
- Subsequent requests should be faster
- Consider using smaller models like `phi` for better performance
- Ensure your server has adequate RAM (8GB+ recommended)

## Privacy

This plugin does not send any data to external services. All AI processing happens on your Ollama server. The plugin only stores:
- Configuration settings (API host, model name)
- No user prompts or responses are stored by this plugin

## Support

For issues related to:
- **Ollama**: Visit https://github.com/ollama/ollama
- **This plugin**: Check the plugin repository or Moodle forums

## License

GPL v3 or later

## Credits

Developed for Moodle's AI subsystem integration.
