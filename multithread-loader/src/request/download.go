package request

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
