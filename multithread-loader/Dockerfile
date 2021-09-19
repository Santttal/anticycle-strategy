FROM golang:1.16

WORKDIR /app
ADD . /app/

RUN go build -o downloader ./src

EXPOSE 8085

CMD ["/app/downloader"]
