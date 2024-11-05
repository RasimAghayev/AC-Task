FROM node:23-alpine
WORKDIR /var/www/html/fe
COPY src/fe/package*.json ./
RUN npm install
COPY src/fe/ .
CMD ["npm", "run", "dev"]