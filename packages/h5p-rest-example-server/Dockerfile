#This is a sample image
FROM node:16

MAINTAINER meet@milliweb.fr, abdoulaye@milliweb.fr

WORKDIR /usr/src/app
SHELL ["/bin/bash", "-c"]

COPY src src
COPY config.json config.json
COPY download-core.sh download-core.sh
COPY package.json package.json
COPY tsconfig.json tsconfig.json
COPY h5p h5p
RUN npm install
RUN chown -R node .
USER node
CMD npm run start
