#!/usr/bin/env sh
set -eu

if [ "$#" -lt 1 ]; then
  echo "Usage: $0 host:port [-- command ...]" >&2
  exit 1
fi

TARGET="$1"
shift

HOST="${TARGET%:*}"
PORT="${TARGET##*:}"
TIMEOUT="${WAITFORIT_TIMEOUT:-60}"
STRICT="${WAITFORIT_STRICT:-1}"

if [ -z "$HOST" ] || [ -z "$PORT" ] || [ "$HOST" = "$PORT" ]; then
  echo "Invalid target '$TARGET' (expected host:port)" >&2
  exit 1
fi

END=$(( $(date +%s) + TIMEOUT ))

echo "Waiting for $HOST:$PORT (timeout: ${TIMEOUT}s)..."

while :; do
  if nc -z "$HOST" "$PORT" >/dev/null 2>&1; then
    echo "$HOST:$PORT is available"
    break
  fi

  if [ "$(date +%s)" -ge "$END" ]; then
    echo "Timeout while waiting for $HOST:$PORT" >&2
    if [ "$STRICT" = "1" ]; then
      exit 1
    fi
    break
  fi

  sleep 1
done

if [ "$#" -gt 0 ] && [ "$1" = "--" ]; then
  shift
  exec "$@"
fi

