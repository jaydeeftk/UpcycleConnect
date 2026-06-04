# syntax=docker/dockerfile:1

# --- Étape de build : binaire statique sans CGO ---
FROM golang:1.23-alpine AS build
WORKDIR /src
# On copie d'abord les manifestes pour profiter du cache de couches.
COPY go.mod go.sum ./
RUN go mod download
COPY . .
# -trimpath + -ldflags "-s -w" : binaire reproductible et allégé.
RUN CGO_ENABLED=0 GOOS=linux go build -trimpath -ldflags="-s -w" -o /out/rgpdapp .

# --- Étape finale : image minimale, exécution non-root ---
FROM alpine:3.20
RUN apk add --no-cache ca-certificates wget \
 && adduser -D -H -u 10001 rgpd
COPY --from=build /out/rgpdapp /usr/local/bin/rgpdapp
USER rgpd
EXPOSE 8090
HEALTHCHECK --interval=15s --timeout=3s --start-period=20s --retries=5 \
  CMD wget -qO- http://127.0.0.1:8090/healthz >/dev/null 2>&1 || exit 1
ENTRYPOINT ["/usr/local/bin/rgpdapp"]
