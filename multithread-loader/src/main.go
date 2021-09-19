package main

import (
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"sync"
)

func main() {
	http.HandleFunc("/download", handler)

	fmt.Printf("Starting server for testing HTTP POST...\n")
	if err := http.ListenAndServe(":80", nil); err != nil {
		log.Fatal(err)
	}
}

func handler(w http.ResponseWriter, r *http.Request) {
	if r.Method != "POST" {
		http.Error(w, "404 not found.", http.StatusNotFound)
		return
	}
	var requestBody DownloadRequest
	var responseBody DownloadResponse
	var wg sync.WaitGroup

	err := json.NewDecoder(r.Body).Decode(&requestBody)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
	}

	responseBody.Items = make([]DownloadItemResponse, len(requestBody.Urls))
	for i, url := range requestBody.Urls {
		wg.Add(1)
		responseBody.Items[i].Url = url
		item := &responseBody.Items[i]

		go func(url string, item *DownloadItemResponse) {
			defer wg.Done()
			resp, err := http.Get(url)
			if err == nil {
				data, _ := io.ReadAll(resp.Body)
				item.Data = string(data)
			} else {
				item.Error = err.Error()
			}
			fmt.Printf("Done.\n")
		}(url, item)
	}
	wg.Wait()

	json.NewEncoder(w).Encode(responseBody)
}

type DownloadRequest struct {
	Urls []string `json:"urls"`
}

type DownloadResponse struct {
	Items []DownloadItemResponse `json:"items"`
}
type DownloadItemResponse struct {
	Url   string `json:"url"`
	Data  string `json:"data"`
	Error string `json:"error"`
}
