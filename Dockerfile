FROM php:8.2-cli

# Install GD extension for image processing
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Create necessary directories
RUN mkdir -p uploads data && chmod 777 uploads data

# Expose port 8080
EXPOSE 8080

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8080"]

