
export class AppError extends Error {
    public appCode: string;
    public httpStatus: number;
    public titleMessage: string;

    constructor(appCode: string, httpStatus: number, message: string, titleMessage: string) {
        super(message);
        this.appCode = appCode;
        this.httpStatus = httpStatus;
        this.titleMessage = titleMessage;
    }
}

