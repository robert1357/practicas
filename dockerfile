# Usa una imagen oficial de PHP con servidor embebido
FROM php:8.2-cli

# Establece la carpeta de trabajo
WORKDIR /app

# Copia todos los archivos de tu proyecto al contenedor
COPY . .

# Comando que ejecutará Railway al iniciar el contenedor
CMD ["php", "-S", "0.0.0.0:8080"]
