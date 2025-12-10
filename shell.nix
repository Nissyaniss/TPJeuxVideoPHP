{
  pkgs ? import <nixpkgs> { },
}:
pkgs.mkShell {
  nativeBuildInputs = with pkgs.buildPackages; [
    php
    phpPackages.composer
    nodejs
    git
    mysql84
  ];
  shellHook = ''
        # Set default port
        PORT=''${PHP_SERVER_PORT:-8000}

        # MySQL Environment Variables
        export MYSQL_HOME=$PWD/.mysql
        export MYSQL_DATADIR=$MYSQL_HOME/data
        export MYSQL_UNIX_PORT=$MYSQL_HOME/mysql.sock
        export MYSQL_PID_FILE=$MYSQL_HOME/mysql.pid
        export MYSQL_TCP_PORT=3306
        
        # Function to check if server is running
        checkServerRunning() {
          if ${pkgs.netcat}/bin/nc -z localhost $1 2>/dev/null; then
            return 0
          else
            return 1
          fi
        }

        # Function to start server
        startServer() {
          local port=''${1:-8000}
          echo "🚀 Starting PHP development server on port $port..."
          
          # Determine document root
          local document_root="."
          if [ -d "public" ]; then
            document_root="public"
          fi
          
          php -S 0.0.0.0:$port -t "$document_root" -d upload_max_filesize=50M -d post_max_size=50M > .php-server.log 2>&1 &
          echo $! > .php-server.pid
          sleep 2
          if checkServerRunning $port; then
            echo "✅ Server started successfully on http://localhost:$port"
            echo "📄 Logs: .php-server.log"
            echo "🆔 PID: $(cat .php-server.pid)"
          else
            echo "❌ Failed to start server. Check .php-server.log for details."
          fi
        }

        # Function to stop server
        stopServer() {
          if [ -f .php-server.pid ]; then
            local PID=$(cat .php-server.pid)
            if kill -0 $PID 2>/dev/null; then
              echo "🛑 Stopping PHP server (PID: $PID)..."
              kill $PID
              rm -f .php-server.pid
              echo "✅ Server stopped"
            else
              echo "⚠️  Server PID found but process not running. Cleaning up..."
              rm -f .php-server.pid
            fi
          else
            echo "ℹ️  No server PID file found. Is the server running?"
          fi
        }

        # Function to show server status
        serverStatus() {
          if [ -f .php-server.pid ]; then
            local PID=$(cat .php-server.pid)
            if kill -0 $PID 2>/dev/null; then
              echo "✅ Server is running (PID: $PID)"
              echo "🌐 URL: http://localhost:''${PHP_SERVER_PORT:-8000}"
              echo "📄 Logs: .php-server.log"
            else
              echo "❌ Server PID file exists but process is not running"
              rm -f .php-server.pid
            fi
          else
            echo "❌ Server is not running"
          fi
        }

        # Function to show server logs
        serverLogs() {
          if [ -f .php-server.log ]; then
            tail -f .php-server.log
          else
            echo "No log file found. Server may not have been started."
          fi
        }

        # MySQL Functions
        setupMysql() {
          if [ ! -d "$MYSQL_DATADIR" ]; then
            echo "⚙️  Initializing MySQL data directory..."
            mkdir -p "$MYSQL_HOME"
            mysqld --initialize-insecure --datadir="$MYSQL_DATADIR" --user=root
            echo "✅ MySQL initialized"
          fi
        }

        startMysql() {
          if [ -f "$MYSQL_PID_FILE" ] && kill -0 $(cat "$MYSQL_PID_FILE") 2>/dev/null; then
             echo "✅ MySQL is already running on socket $MYSQL_UNIX_PORT"
             return
          fi

          echo "🚀 Starting MySQL server..."
          setupMysql
          mysqld --datadir="$MYSQL_DATADIR" --socket="$MYSQL_UNIX_PORT" --pid-file="$MYSQL_PID_FILE" --daemonize --port="$MYSQL_TCP_PORT" --bind-address=127.0.0.1 --log-error="$MYSQL_HOME/mysql.log"
          
          # Wait for startup
          for i in {1..10}; do
            if [ -S "$MYSQL_UNIX_PORT" ]; then
               echo "✅ MySQL started successfully"
               return
            fi
            sleep 1
          done
          echo "❌ Failed to start MySQL"
        }

        stopMysql() {
          if [ -f "$MYSQL_PID_FILE" ]; then
            local pid=$(cat "$MYSQL_PID_FILE")
            echo "🛑 Stopping MySQL (PID: $pid)..."
            kill $pid 2>/dev/null
            rm -f "$MYSQL_PID_FILE"
            echo "✅ MySQL stopped"
          else
            echo "ℹ️  MySQL is not running"
          fi
        }
        
       mysqlStatus() {
           if [ -f "$MYSQL_PID_FILE" ] && kill -0 $(cat "$MYSQL_PID_FILE") 2>/dev/null; then
              echo "✅ MySQL is running"
              echo "🔌 Socket: $MYSQL_UNIX_PORT"
              echo "🔌 Port: $MYSQL_TCP_PORT"
              echo "📂 Data: $MYSQL_DATADIR"
           else
              echo "❌ MySQL is not running"
           fi
        }

        mysqlLogs() {
          if [ -f "$MYSQL_HOME/mysql.log" ]; then
            tail -f "$MYSQL_HOME/mysql.log"
          else
            echo "No log file found. Server may not have been started."
          fi
        }

        echo "🐘 PHP development environment ready!"
        echo "PHP version: $(php -v | head -n1)"
        echo "Composer version: $(composer --version)"
        
        # Check if vendor directory exists, if not run composer install
        if [ ! -d "vendor" ] && [ -f "composer.json" ]; then
          echo "📦 Installing Composer dependencies..."
          composer install
        fi
        
        # Check if server is already running
        if checkServerRunning $PORT; then
          echo "✅ Server is already running on port $PORT"
          echo "🌐 Access your app at: http://localhost:$PORT"
        else
          # Clean up any stale PID file
          if [ -f .php-server.pid ]; then
            rm -f .php-server.pid
          fi
          
          # Auto-start services
          startMysql
          startServer $PORT
        fi

        echo ""
        echo "🔧 Available commands in this shell:"
        echo "   startServer [port] - Start the PHP development server"
        echo "   stopServer        - Stop the PHP development server" 
        echo "   serverStatus      - Check if server is running
       serverLogs        - Tail server logs
       startMysql        - Start MySQL server
       stopMysql         - Stop MySQL server
       mysqlStatus       - Check MySQL status
       mysqlLogs         - Tail MySQL logs
    "
        echo "   composer           - Manage dependencies"
        echo "   php artisan        - Laravel commands (if applicable)"
        echo ""
        echo "💡 You can set custom port: export PHP_SERVER_PORT=3000"
  '';
}
