#!/bin/bash
source /root/.bash_profile

# MIRATE
DIR_APP_ROOT=/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app
DIR_MICROSERVICES=/home/YOUR_USERNAME/htdocs/YOUR_DOMAIN/saas/app/microservices

# MIGRATE (Otherwise python could files as root and become unreadable)
# USER=root


# REQ: Initiate pyenv and pyenv-virtualenv
export PYENV_ROOT="$HOME/.pyenv"
export PATH="$PYENV_ROOT/bin:$PATH"
if command -v pyenv 1>/dev/null 2>&1; then
    eval "$(pyenv init -)"
    eval "$(pyenv virtualenv-init -)"
fi

cd $DIR_APP_ROOT
pyenv activate APP_NAME

# CASE: ImageMagick which MoviePy uses
export MAGICK_HOME="$(dirname $(which convert))"

# CASE: Worker timeouts can be caught
#   Function to handle termination signals
term_handler() {
  echo "Termination signal received, stopping Gunicorn..."
  kill -TERM "$gunicorn_pid" 2>/dev/null
}
#   Trap termination signals
trap 'term_handler' SIGTERM SIGINT

# SERVER: SSL PATHS via .env.local
cd $DIR_APP_ROOT
export $(grep -v '^#' ./.env.local | xargs)
# echo $SSL_CERT_PATH


# SERVER: Start Gunicorn
cd $DIR_MICROSERVICES

# Production:
# gunicorn video_wsgi:video_engine -b 0.0.0.0:5002 --worker-class=gevent --timeout=600 --workers=22 --threads=1 --worker-connections=100 --max-requests=1000 --max-requests-jitter=100 --certfile="$SSL_CERT_PATH" --keyfile="$SSL_KEY_PATH"
PYTHONUNBUFFERED=1 gunicorn video_wsgi:video_engine -b 127.0.0.1:5002 --worker-class=gevent --timeout=600 --workers=22 --threads=1 --worker-connections=100 --max-requests=1000 --max-requests-jitter=100 --log-level=debug --capture-output

# Debug with `supervisord -c /etc/supervisor/supervisord.conf -l /var/log/supervisor/supervisord.log -n`:
# PYTHONUNBUFFERED=1 gunicorn video_wsgi:video_engine -b 127.0.0.1:5002 --worker-class=gevent --timeout=600 --workers=1 --threads=1 --worker-connections=100 --max-requests=1000 --max-requests-jitter=100 --log-level=debug --capture-output


# CASE: Worker timeouts can be caught... Continue
#   Capture Gunicorn PID
gunicorn_pid=$!
echo "Gunicorn PID: $gunicorn_pid"
#   Wait for Gunicorn process
wait "$gunicorn_pid"
