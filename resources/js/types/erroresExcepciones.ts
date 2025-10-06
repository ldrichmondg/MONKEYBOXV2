
export class AppError extends Error {
    public appCode: string;
    public httpStatus: number;
    public titleMessage: string;
    public data: never[];

    constructor(appCode: string, httpStatus: number, message: string, titleMessage: string, data?: never[]) {
        super(message);
        this.appCode = appCode;
        this.httpStatus = httpStatus;
        this.titleMessage = titleMessage;
        if(data)
            this.data = data;
    }
}

